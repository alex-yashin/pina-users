<?php

namespace PinaUsers;

use Exception;
use Pina\App;
use PinaUsers\SQL\UserGateway;
use PinaUsers\Types\EmailType;
use PinaUsers\SQL\AuthGateway;
use Pina\Types\ValidateException;

class Auth
{

    const EXPIRATION_INTERVAL = 360000;
    protected $userId = 0;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $cookie = $this->parseCookie();
        if (empty($cookie)) {
            return;
        }

        $authId = $this->makeAuthId($cookie);

        $this->userId = AuthGateway::instance()
            ->whereId($authId)
            ->whereNotExpired()
            ->userId();

        if (!empty($this->userId)) {
            $this->renew($authId, $cookie);
        }
    }

    /**
     * @param string $email
     * @param string $password
     * @return bool
     * @throws Exception
     */
    public function attempt(string $email, string $password)
    {
        $this->userId = $this->validate($email, $password);
        if (empty($this->userId)) {
            return false;
        }

        $this->removeExpired();

        return $this->start($this->userId);
    }

    /**
     * @param string $email
     * @param string $password
     * @return bool
     * @throws Exception
     */
    public function once(string $email, string $password)
    {
        $this->userId = $this->validate($email, $password);

        return !empty($this->userId);
    }

    /**
     * @param $userId
     * @return bool
     * @throws Exception
     */
    public function loginUsingId($userId)
    {
        if (!$this->userExists($userId)) {
            return false;
        }

        $this->userId = $userId;

        return $this->start($userId);
    }

    public function userId()
    {
        return $this->userId;
    }

    /**
     * @throws Exception
     */
    public function logout()
    {
        $cookie = $this->parseCookie();
        if (empty($cookie)) {
            AuthGateway::instance()
                ->whereBy('user_id', $this->userId)
                ->whereBy('user_agent', $this->parseUserAgent())
                ->delete();
        } else {
            $this->sendCookie('');
            AuthGateway::instance()
                ->whereId($this->makeAuthId($cookie))
                ->delete();
        }
        $this->userId = null;
    }

    public function isSignedIn()
    {
        return !empty($this->userId);
    }

    /**
     * @param string $login
     * @param string $password
     * @return mixed|null
     * @throws Exception
     */
    protected function validate(string $login, string $password)
    {
        if (empty($password) || empty($login)) {
            return null;
        }

        $user = UserGateway::instance()
            ->whereBy('enabled', 'Y')
            ->whereBy('email', $login)
            ->select('id')
            ->select('password')
            ->first();

        if (!isset($user['id'])) {
            return null;
        }

        if (!Hash::check($password, $user["password"])) {
            return null;
        }

        return $user['id'];
    }

    /**
     * @param $login
     * @return bool
     * @throws Exception
     */
    protected function isEmail($login)
    {
        try {
            App::type(EmailType::class)->normalize($login, true);
        } catch (ValidateException $e) {
            return false;
        }
        return true;
    }

    /**
     * @param $userId
     * @return bool
     * @throws Exception
     */
    protected function userExists($userId)
    {
        return UserGateway::instance()
            ->whereId($userId)
            ->whereBy('enabled', 'Y')
            ->exists();
    }

    protected function makeAuthId($pnid)
    {
        return md5($pnid . $this->parseUserAgent());
    }

    protected function removeExpired()
    {
        AuthGateway::instance()->whereExpired()->delete();
    }

    /**
     * @param $userId
     * @return bool
     * @throws Exception
     */
    protected function start($userId)
    {
        $cookie = $this->generateCookie();
        $data = [
            'id' => $this->makeAuthId($cookie),
            'user_id' => $userId,
            'user_agent' => $this->parseUserAgent(),
            'ip' => self::parseClientIp(),
        ];

        if (!AuthGateway::instance()->add($data)) {
            return false;
        }

        return $this->sendCookie($cookie);
    }

    /**
     * @param $authId
     * @param $cookie
     * @return bool
     * @throws Exception
     */
    protected function renew($authId, $cookie)
    {
        $this->sendCookie($cookie);

        AuthGateway::instance()->whereId($authId)->renew();

        return true;
    }

    protected function sendCookie($cookie): bool
    {
        if (isset($_ENV['MODE']) && $_ENV['MODE'] == 'test') {
            return true;
        }

        $expired = empty($cookie) ? 0 : time() + static::EXPIRATION_INTERVAL;

        return setcookie('pnid', $cookie, $expired, '/', '', false, true);
    }

    protected function generateCookie()
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
        $code = "";
        $length = mt_rand(0, 32);

        $clen = strlen($chars) - 1;
        while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0, $clen)];
        }

        return uniqid($code . time(), true);
    }

    protected function parseCookie()
    {
        return $_COOKIE['pnid'] ?? '';
    }

    protected function parseUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    protected function parseClientIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $_SERVER['REMOTE_ADDR'] ?? '';
    }


}
