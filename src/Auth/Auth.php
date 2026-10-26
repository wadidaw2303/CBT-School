<?php

declare(strict_types=1);

namespace Auth;

use Core\Database;
use Core\Session;
use Core\Csrf;

class Auth
{
    private static ?self $instance = null;
    private ?User $user = null;
    private bool $userResolved = false;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function user(): ?User
    {
        if ($this->userResolved) {
            return $this->user;
        }

        $this->userResolved = true;

        Session::init();

        if (isset($_SESSION['user_id'])) {
            $this->user = User::find((int) $_SESSION['user_id']);
            if ($this->user && !$this->user->is_active) {
                $this->logout();
                return null;
            }
            return $this->user;
        }

        if (isset($_COOKIE['remember_token'])) {
            return $this->loginViaRememberToken($_COOKIE['remember_token']);
        }

        return null;
    }

    public function id(): ?int
    {
        return $this->user()?->id;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function attempt(string $email, string $password, bool $remember = false): bool
    {
        $user = User::findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            return false;
        }

        if (!$user->is_active) {
            Session::flash('error', 'Akun Anda tidak aktif. Hubungi administrator.');
            return false;
        }

        return $this->login($user, $remember);
    }

    public function login(User $user, bool $remember = false): bool
    {
        Session::regenerate();
        $_SESSION['user_id'] = $user->id;
        $_SESSION['role_id'] = $user->role_id;

        $user->setLastLogin();

        $this->user = $user;
        $this->userResolved = true;

        if ($remember) {
            $this->setRememberToken($user);
        }

        Csrf::regenerateToken();

        log_activity('login', 'users', $user->id);

        return true;
    }

    public function logout(): void
    {
        $user = $this->user();

        if ($user) {
            log_activity('logout', 'users', $user->id);
        }

        $this->clearRememberToken();
        Session::destroy();
        $this->user = null;
        $this->userResolved = false;
    }

    public function loginViaRememberToken(string $token): ?User
    {
        $hashedToken = hash('sha256', $token);

        $data = Database::getInstance()->table('users')
            ->where('remember_token', '=', $hashedToken)
            ->where('remember_token_expires', '>', date('Y-m-d H:i:s'))
            ->first();

        if (!$data) {
            $this->clearRememberToken();
            return null;
        }

        $user = new User($data);

        if (!$user->is_active) {
            $this->clearRememberToken();
            return null;
        }

        return $this->login($user, true) ? $user : null;
    }

    private function setRememberToken(User $user): void
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

        Database::getInstance()->table('users')->where('id', '=', $user->id)->update([
            'remember_token' => $hashedToken,
            'remember_token_expires' => $expires,
        ]);

        setcookie('remember_token', $token, [
            'expires' => strtotime($expires),
            'path' => '/',
            'domain' => '',
            'secure' => config('session.secure_cookie', false),
            'httponly' => true,
            'samesite' => 'lax',
        ]);
    }

    private function clearRememberToken(): void
    {
        if ($this->user) {
            Database::getInstance()->table('users')->where('id', '=', $this->user->id)->update([
                'remember_token' => null,
                'remember_token_expires' => null,
            ]);
        }

        setcookie('remember_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => config('session.secure_cookie', false),
            'httponly' => true,
            'samesite' => 'lax',
        ]);
    }

    public function validateCredentials(string $email, string $password): ?User
    {
        $user = User::findByEmail($email);
        if ($user && $user->verifyPassword($password) && $user->is_active) {
            return $user;
        }
        return null;
    }

    public function sendPasswordResetLink(string $email): bool
    {
        $user = User::findByEmail($email);
        if (!$user) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        Database::getInstance()->table('password_resets')->insert([
            'email' => $email,
            'token' => hash('sha256', $token),
            'expires_at' => $expires,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // TODO: Send email with reset link
        // For now, store in session for testing
        Session::flash('reset_token', $token);
        Session::flash('reset_email', $email);

        log_activity('password_reset_requested', 'users', $user->id);

        return true;
    }

    public function resetPassword(string $email, string $token, string $password): bool
    {
        $hashedToken = hash('sha256', $token);

        $reset = Database::getInstance()->table('password_resets')
            ->where('email', '=', $email)
            ->where('token', '=', $hashedToken)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if (!$reset) {
            return false;
        }

        $user = User::findByEmail($email);
        if (!$user) {
            return false;
        }

        $user->update(['password' => $password]);

        Database::getInstance()->table('password_resets')
            ->where('email', '=', $email)
            ->delete();

        log_activity('password_reset', 'users', $user->id);

        return true;
    }

    public function validateResetToken(string $email, string $token): bool
    {
        $hashedToken = hash('sha256', $token);

        return Database::getInstance()->table('password_resets')
            ->where('email', '=', $email)
            ->where('token', '=', $hashedToken)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->exists();
    }

    public function getUserByResetToken(string $email, string $token): ?User
    {
        $hashedToken = hash('sha256', $token);

        $reset = Database::getInstance()->table('password_resets')
            ->where('email', '=', $email)
            ->where('token', '=', $hashedToken)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if (!$reset) {
            return null;
        }

        return User::findByEmail($email);
    }
}