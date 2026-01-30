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
    protected $userId = null;

    public static function load(): Auth
    {
        return App::load(static::class);
    }

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
     * @throws Exception
     */
    public function attempt(string $email, string $password): bool
    {
        $this->userId = $this->validate($email, $password);
        if (empty($this->userId)) {
            return false;
        }

        $this->removeExpired();

        return $this->start($this->userId);
    }

    /**
     * @throws Exception
     */
    public function once(string $email, string $password): bool
    {
        $this->userId = $this->validate($email, $password);

        return !empty($this->userId);
    }

    /**
     * @throws Exception
     */
    public function loginUsingId($userId): bool
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

    public function isSignedIn(): bool
    {
        return !empty($this->userId);
    }

    /**
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
     * @return bool
     * @throws Exception
     */
    protected function isEmail(string $login): bool
    {
        try {
            App::type(EmailType::class)->normalize($login, true);
        } catch (ValidateException $e) {
            return false;
        }
        return true;
    }

    /**
     * @throws Exception
     */
    protected function userExists($userId): bool
    {
        return UserGateway::instance()
            ->whereId($userId)
            ->whereBy('enabled', 'Y')
            ->exists();
    }

    protected function makeAuthId(string $pnid): string
    {
        return md5($pnid . $this->parseUserAgent());
    }

    protected function removeExpired()
    {
        AuthGateway::instance()->whereExpired()->delete();
    }

    /**
     * @throws Exception
     */
    protected function start($userId): bool
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
     * @throws Exception
     */
    protected function renew(string $authId, string $cookie): bool
    {
        $this->sendCookie($cookie);

        AuthGateway::instance()->whereId($authId)->renew();

        return true;
    }

    protected function sendCookie(string $cookie): bool
    {
        if (isset($_ENV['MODE']) && $_ENV['MODE'] == 'test') {
            return true;
        }

        $expired = empty($cookie) ? 0 : time() + static::EXPIRATION_INTERVAL;

        return setcookie('pnid', $cookie, $expired, '/', '', false, true);
    }

    protected function generateCookie(): string
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

    protected function parseCookie(): string
    {
        return $_COOKIE['pnid'] ?? '';
    }

    protected function parseUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    protected function parseClientIp(): string
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
