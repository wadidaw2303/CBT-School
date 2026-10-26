<?php

declare(strict_types=1);

namespace Core;

/**
 * Global helper functions
 */

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return Config::getInstance()->get($key, $default);
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? getenv($key);
        if ($value === false || $value === '') {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        if (is_numeric($value)) {
            return (float) $value === (int) $value ? (int) $value : (float) $value;
        }

        return $value;
    }
}

if (!function_exists('uuid')) {
    function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}

if (!function_exists('h')) {
    function h(string $string, int $flags = ENT_QUOTES | ENT_SUBSTITUTE, string $encoding = 'UTF-8'): string
    {
        return htmlspecialchars($string, $flags, $encoding);
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR);
        }
        return h((string) $value);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim(config('app.url', ''), '/');
        $path = ltrim($path, '/');
        return $path ? "{$base}/{$path}" : $base;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $base = rtrim(config('app.url', ''), '/');
        $path = ltrim($path, '/');
        return "{$base}/{$path}";
    }
}

if (!function_exists('route')) {
    function route(string $name, array $params = []): string
    {
        $router = App::getInstance()->getRouter();
        return $router->route($name, $params);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $status = 302): Response
    {
        return new Response('', $status, ['Location' => $url]);
    }
}

if (!function_exists('back')) {
    function back(int $status = 302): Response
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        return redirect($referer, $status);
    }
}

if (!function_exists('abort')) {
    function abort(int $code, string $message = ''): never
    {
        http_response_code($code);
        if ($message) {
            echo $message;
        }
        exit;
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): never
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
        exit;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::getToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . h(csrf_token()) . '">';
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return Session::getOldInput($key, $default);
    }
}

if (!function_exists('flash')) {
    function flash(string $key, string $message = ''): void
    {
        if ($message) {
            Session::flash($key, $message);
        } else {
            Session::getFlash($key);
        }
    }
}

if (!function_exists('has_flash')) {
    function has_flash(string $key): bool
    {
        return Session::hasFlash($key);
    }
}

if (!function_exists('get_flash')) {
    function get_flash(string $key, string $default = ''): string
    {
        return Session::getFlash($key, $default);
    }
}

if (!function_exists('method_field')) {
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . h(strtoupper($method)) . '">';
    }
}

if (!function_exists('str_slug')) {
    function str_slug(string $string, string $separator = '-'): string
    {
        $string = mb_strtolower(trim($string), 'UTF-8');
        $string = preg_replace('/[^\p{L}\p{N}]+/u', $separator, $string);
        $string = trim($string, $separator);
        return $string;
    }
}

if (!function_exists('str_limit')) {
    function str_limit(string $string, int $limit = 100, string $end = '...'): string
    {
        if (mb_strlen($string) <= $limit) {
            return $string;
        }
        return mb_substr($string, 0, $limit - mb_strlen($end)) . $end;
    }
}

if (!function_exists('format_date')) {
    function format_date(\DateTimeInterface|string|null $date, string $format = 'd M Y'): string
    {
        if ($date === null) {
            return '-';
        }
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        return $date->format($format);
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime(\DateTimeInterface|string|null $date, string $format = 'd M Y H:i'): string
    {
        if ($date === null) {
            return '-';
        }
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        return $date->format($format);
    }
}

if (!function_exists('time_ago')) {
    function time_ago(\DateTimeInterface|string $date): string
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        $now = new \DateTime();
        $diff = $now->diff($date);

        if ($diff->y > 0) return $diff->y . ' tahun yang lalu';
        if ($diff->m > 0) return $diff->m . ' bulan yang lalu';
        if ($diff->d > 0) return $diff->d . ' hari yang lalu';
        if ($diff->h > 0) return $diff->h . ' jam yang lalu';
        if ($diff->i > 0) return $diff->i . ' menit yang lalu';
        return 'Baru saja';
    }
}

if (!function_exists('format_bytes')) {
    function format_bytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('array_get')) {
    function array_get(array $array, string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        foreach ($keys as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }
        return $array;
    }
}

if (!function_exists('array_set')) {
    function array_set(array &$array, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        foreach ($keys as $segment) {
            if (!isset($array[$segment]) || !is_array($array[$segment])) {
                $array[$segment] = [];
            }
            $array = &$array[$segment];
        }
        $array[$lastKey] = $value;
    }
}

if (!function_exists('collect')) {
    function collect(iterable $items = []): array
    {
        return is_array($items) ? $items : iterator_to_array($items);
    }
}

if (!function_exists('auth')) {
    function auth(): ?Auth\User
    {
        return Auth::getInstance()->user();
    }
}

if (!function_exists('guest')) {
    function guest(): bool
    {
        return auth() === null;
    }
}

if (!function_exists('can')) {
    function can(string $module): bool
    {
        return \App\Support\ModuleAccess::bladeCan($module);
    }
}

if (!function_exists('role')) {
    function role(string $role): bool
    {
        $user = auth();
        return $user?->hasRole($role) ?? false;
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return role('admin');
    }
}

if (!function_exists('is_teacher')) {
    function is_teacher(): bool
    {
        return role('teacher');
    }
}

if (!function_exists('is_student')) {
    function is_student(): bool
    {
        return role('student');
    }
}

if (!function_exists('view')) {
    function view(string $template, array $data = []): string
    {
        return View::render($template, $data);
    }
}

if (!function_exists('json_response')) {
    function json_response(mixed $data, int $status = 200, array $headers = []): Response
    {
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        return new Response(json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $status, $headers);
    }
}

if (!function_exists('validator')) {
    function validator(array $data, array $rules, array $messages = []): Validator
    {
        return new Validator($data, $rules, $messages);
    }
}

if (!function_exists('request')) {
    function request(): Request
    {
        return App::getInstance()->getRequest();
    }
}

if (!function_exists('response')) {
    function response(mixed $content = '', int $status = 200, array $headers = []): Response
    {
        return new Response((string) $content, $status, $headers);
    }
}

if (!function_exists('database')) {
    function database(): Database
    {
        return App::getInstance()->getDatabase();
    }
}

if (!function_exists('session')) {
    function session(): Session
    {
        return App::getInstance()->getSession();
    }
}

if (!function_exists('log_activity')) {
    function log_activity(string $action, string $modelType, int $modelId, array $old = [], array $new = []): void
    {
        $user = auth();
        $db = database();
        $db->insert('activity_logs', [
            'user_id' => $user?->id,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $old ? json_encode($old, JSON_THROW_ON_ERROR) : null,
            'new_values' => $new ? json_encode($new, JSON_THROW_ON_ERROR) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}