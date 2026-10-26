<?php

declare(strict_types=1);

namespace Core;

class Session
{
    private static bool $started = false;
    private static array $flashData = [];
    private static array $oldInput = [];
    private static string $driver = 'file';
    private static int $lifetime = 120;
    private static bool $secureCookie = false;
    private static bool $httpOnly = true;
    private static string $sameSite = 'lax';

    public static function init(): void
    {
        if (self::$started) {
            return;
        }

        self::$driver = config('session.driver', 'file');
        self::$lifetime = (int) config('session.lifetime', 120);
        self::$secureCookie = config('session.secure_cookie', false);
        self::$httpOnly = config('session.http_only', true);
        self::$sameSite = config('session.same_site', 'lax');

        if (self::$driver === 'file') {
            $path = sys_get_temp_dir() . '/cbt_sessions';
            if (!is_dir($path)) {
                mkdir($path, 0700, true);
            }
            session_save_path($path);
        }

        $cookieParams = [
            'lifetime' => self::$lifetime * 60,
            'path' => '/',
            'domain' => '',
            'secure' => self::$secureCookie,
            'httponly' => self::$httpOnly,
            'samesite' => self::$sameSite,
        ];
        session_set_cookie_params($cookieParams);

        session_start([
            'use_strict_mode' => true,
            'read_and_close' => false,
        ]);

        self::$started = true;

        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        if (!empty($_SESSION['_flash'])) {
            self::$flashData = $_SESSION['_flash'];
            unset($_SESSION['_flash']);
        }

        if (!empty($_SESSION['_old_input'])) {
            self::$oldInput = $_SESSION['_old_input'];
            unset($_SESSION['_old_input']);
        }

        self::regenerateIfNeeded();
    }

    public static function regenerate(bool $deleteOld = true): void
    {
        if (!self::$started) {
            self::init();
        }
        session_regenerate_id($deleteOld);
        $_SESSION['_last_regeneration'] = time();
    }

    private static function regenerateIfNeeded(): void
    {
        $lastRegeneration = $_SESSION['_last_regeneration'] ?? 0;
        if (time() - $lastRegeneration > 300) {
            self::regenerate();
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$started) {
            self::init();
        }
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        if (!self::$started) {
            self::init();
        }
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        if (!self::$started) {
            self::init();
        }
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        if (!self::$started) {
            self::init();
        }
        unset($_SESSION[$key]);
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = self::get($key, $default);
        self::forget($key);
        return $value;
    }

    public static function flush(): void
    {
        if (!self::$started) {
            self::init();
        }
        $_SESSION = [];
    }

    public static function flash(string $key, string $message): void
    {
        if (!self::$started) {
            self::init();
        }
        $_SESSION['_flash'][$key] = $message;
    }

    public static function getFlash(string $key, string $default = ''): string
    {
        if (!self::$started) {
            self::init();
        }
        return self::$flashData[$key] ?? $default;
    }

    public static function hasFlash(string $key): bool
    {
        if (!self::$started) {
            self::init();
        }
        return isset(self::$flashData[$key]);
    }

    public static function keepFlash(string ...$keys): void
    {
        foreach ($keys as $key) {
            if (isset(self::$flashData[$key])) {
                self::flash($key, self::$flashData[$key]);
            }
        }
    }

    public static function reflash(): void
    {
        if (!self::$started) {
            self::init();
        }
        $_SESSION['_flash'] = self::$flashData;
    }

    public static function flashInput(array $input): void
    {
        if (!self::$started) {
            self::init();
        }
        $_SESSION['_old_input'] = $input;
    }

    public static function getOldInput(string $key, mixed $default = ''): mixed
    {
        if (!self::$started) {
            self::init();
        }
        return self::$oldInput[$key] ?? $default;
    }

    public static function hasOldInput(string $key): bool
    {
        if (!self::$started) {
            self::init();
        }
        return isset(self::$oldInput[$key]);
    }

    public static function id(): string
    {
        if (!self::$started) {
            self::init();
        }
        return session_id();
    }

    public static function destroy(): void
    {
        if (!self::$started) {
            self::init();
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
        self::$started = false;
    }

    public static function setDriver(string $driver): void
    {
        self::$driver = $driver;
    }

    public static function setLifetime(int $minutes): void
    {
        self::$lifetime = $minutes;
    }
}