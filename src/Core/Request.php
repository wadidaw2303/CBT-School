<?php

declare(strict_types=1);

namespace Core;

class Request
{
    private array $query;
    private array $request;
    private array $files;
    private array $cookies;
    private array $server;
    private array $headers;
    private ?string $method = null;
    private ?string $pathInfo = null;
    private ?string $url = null;
    private mixed $json = null;

    public function __construct()
    {
        $this->query = $_GET;
        $this->request = $_POST;
        $this->files = $_FILES;
        $this->cookies = $_COOKIE;
        $this->server = $_SERVER;
        $this->headers = $this->parseHeaders();
    }

    public static function capture(): self
    {
        return new self();
    }

    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$header] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }

    public function getMethod(): string
    {
        if ($this->method !== null) {
            return $this->method;
        }

        $method = $this->server['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'POST') {
            $override = $this->request['_method'] ?? $this->headers['X-Http-Method-Override'] ?? '';
            if ($override) {
                $method = strtoupper($override);
            }
        }

        return $this->method = $method;
    }

    public function setMethod(string $method): void
    {
        $this->method = strtoupper($method);
    }

    public function is(string ...$methods): bool
    {
        return in_array($this->getMethod(), array_map('strtoupper', $methods), true);
    }

    public function getPathInfo(): string
    {
        if ($this->pathInfo !== null) {
            return $this->pathInfo;
        }

        $pathInfo = $this->server['REQUEST_URI'] ?? '/';
        if (($pos = strpos($pathInfo, '?')) !== false) {
            $pathInfo = substr($pathInfo, 0, $pos);
        }

        $basePath = rtrim(dirname($this->server['SCRIPT_NAME'] ?? ''), '/');
        if ($basePath && $basePath !== '/' && str_starts_with($pathInfo, $basePath)) {
            $pathInfo = substr($pathInfo, strlen($basePath));
        }

        return $this->pathInfo = $pathInfo !== '' ? $pathInfo : '/';
    }

    public function getUrl(): string
    {
        if ($this->url !== null) {
            return $this->url;
        }

        $scheme = $this->isSecure() ? 'https' : 'http';
        $host = $this->server['HTTP_HOST'] ?? $this->server['SERVER_NAME'] ?? 'localhost';
        $port = $this->server['SERVER_PORT'] ?? 80;

        if (($scheme === 'http' && $port !== 80) || ($scheme === 'https' && $port !== 443)) {
            $host .= ':' . $port;
        }

        return $this->url = $scheme . '://' . $host . $this->getPathInfo();
    }

    public function fullUrl(): string
    {
        $query = $this->getQueryString();
        return $query ? $this->getUrl() . '?' . $query : $this->getUrl();
    }

    public function getQueryString(): string
    {
        return $this->server['QUERY_STRING'] ?? '';
    }

    public function isSecure(): bool
    {
        if (isset($this->server['HTTPS']) && $this->server['HTTPS'] === 'on') {
            return true;
        }
        if (isset($this->server['HTTP_X_FORWARDED_PROTO']) && $this->server['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        }
        if (isset($this->server['HTTP_X_FORWARDED_SSL']) && $this->server['HTTP_X_FORWARDED_SSL'] === 'on') {
            return true;
        }
        return (isset($this->server['SERVER_PORT']) && $this->server['SERVER_PORT'] === 443);
    }

    public function ip(): string
    {
        $headers = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP'];
        foreach ($headers as $header) {
            if (!empty($this->server[$header])) {
                $ips = explode(',', $this->server[$header]);
                return trim($ips[0]);
            }
        }
        return $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function header(string $key, mixed $default = null): mixed
    {
        $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
        return $this->headers[$key] ?? $default;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function query(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    public function input(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->request;
        }
        $keys = explode('.', $key);
        $value = $this->request;
        foreach ($keys as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }
        return $value;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->request);
    }

    public function only(array|string $keys): array
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->input($key);
        }
        return $results;
    }

    public function except(array|string $keys): array
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $results = $this->all();
        foreach ($keys as $key) {
            unset($results[$key]);
        }
        return $results;
    }

    public function has(string $key): bool
    {
        $keys = explode('.', $key);
        $value = $this->request;
        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return false;
            }
            $value = $value[$segment];
        }
        return true;
    }

    public function filled(string $key): bool
    {
        $value = $this->input($key);
        return $value !== null && $value !== '' && $value !== [];
    }

    public function file(string $key = null): mixed
    {
        if ($key === null) {
            return $this->files;
        }
        $keys = explode('.', $key);
        $value = $this->files;
        foreach ($keys as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return null;
            }
        }
        return $value;
    }

    public function hasFile(string $key): bool
    {
        $file = $this->file($key);
        if ($file === null) return false;
        if (is_array($file) && isset($file['tmp_name'])) {
            return is_uploaded_file($file['tmp_name']);
        }
        return false;
    }

    public function cookie(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->cookies;
        }
        return $this->cookies[$key] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization');
        if ($header && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        return null;
    }

    public function json(): mixed
    {
        if ($this->json !== null) {
            return $this->json;
        }

        $contentType = $this->header('Content-Type');
        if ($contentType && str_contains($contentType, 'application/json')) {
            $content = file_get_contents('php://input');
            $this->json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        }

        return $this->json;
    }

    public function merge(array $data): void
    {
        $this->request = array_merge($this->request, $data);
    }

    public function replace(array $data): void
    {
        $this->request = $data;
    }

    public function getContent(): string
    {
        return file_get_contents('php://input');
    }

    public function getAcceptableContentTypes(): array
    {
        $accept = $this->header('Accept', '*/*');
        return array_map('trim', explode(',', $accept));
    }

    public function prefers(string $type): bool
    {
        $acceptable = $this->getAcceptableContentTypes();
        foreach ($acceptable as $accept) {
            if (str_starts_with($accept, $type) || $accept === '*/*') {
                return true;
            }
        }
        return false;
    }

    public function expectsJson(): bool
    {
        return $this->prefers('application/json') || $this->isXmlHttpRequest();
    }

    public function isXmlHttpRequest(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public function getScheme(): string
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    public function getHost(): string
    {
        return $this->server['HTTP_HOST'] ?? $this->server['SERVER_NAME'] ?? 'localhost';
    }

    public function getPort(): int
    {
        return (int) ($this->server['SERVER_PORT'] ?? 80);
    }

    public function getSchemeAndHttpHost(): string
    {
        return $this->getScheme() . '://' . $this->getHost();
    }

    public function getBaseUrl(): string
    {
        return $this->getSchemeAndHttpHost() . rtrim(dirname($this->server['SCRIPT_NAME'] ?? ''), '/');
    }

    public function ajax(): bool
    {
        return $this->isXmlHttpRequest();
    }

    public function pjax(): bool
    {
        return $this->header('X-PJAX') === 'true';
    }

    public function fingerprint(): string
    {
        return hash('sha256', $this->ip() . '|' . $this->userAgent());
    }
}