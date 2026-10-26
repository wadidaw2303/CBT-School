<?php

declare(strict_types=1);

namespace Core;

class Csrf
{
    private const TOKEN_NAME = '_token';
    private const HEADER_NAME = 'X-CSRF-TOKEN';
    private const SESSION_KEY = '_csrf_token';

    public static function getToken(): string
    {
        Session::init();
        return $_SESSION[self::SESSION_KEY] ??= bin2hex(random_bytes(32));
    }

    public static function regenerateToken(): string
    {
        Session::init();
        $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        return $_SESSION[self::SESSION_KEY];
    }

    public static function verify(?string $token = null): bool
    {
        Session::init();

        $token ??= $_POST[self::TOKEN_NAME] ?? $_SERVER['HTTP_' . str_replace('-', '_', strtoupper(self::HEADER_NAME))] ?? null;

        if ($token === null) {
            return false;
        }

        $sessionToken = $_SESSION[self::SESSION_KEY] ?? '';

        return hash_equals($sessionToken, $token);
    }

    public static function verifyOrFail(?string $token = null): void
    {
        if (!self::verify($token)) {
            abort(419, 'CSRF token mismatch. Page may have expired. Please refresh and try again.');
        }
    }

    public static function field(): string
    {
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . h(self::getToken()) . '">';
    }

    public static function metaTag(): string
    {
        return '<meta name="csrf-token" content="' . h(self::getToken()) . '">';
    }

    public static function tokenHeader(): array
    {
        return [self::HEADER_NAME => self::getToken()];
    }
}