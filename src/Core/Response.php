<?php

declare(strict_types=1);

namespace Core;

class Response
{
    private string $content = '';
    private int $statusCode = 200;
    private array $headers = [];
    private string $protocolVersion = '1.1';
    private bool $sent = false;

    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = array_merge([
            'Content-Type' => 'text/html; charset=utf-8',
            'X-Powered-By' => 'CBT School',
        ], $headers);
    }

    public static function create(string $content = '', int $statusCode = 200, array $headers = []): self
    {
        return new self($content, $statusCode, $headers);
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $key): ?string
    {
        $key = strtolower($key);
        foreach ($this->headers as $header => $value) {
            if (strtolower($header) === $key) {
                return $value;
            }
        }
        return null;
    }

    public function setHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function hasHeader(string $key): bool
    {
        $key = strtolower($key);
        foreach ($this->headers as $header => $value) {
            if (strtolower($header) === $key) {
                return true;
            }
        }
        return false;
    }

    public function removeHeader(string $key): self
    {
        $key = strtolower($key);
        $this->headers = array_filter($this->headers, fn($v, $k) => strtolower($k) !== $key, ARRAY_FILTER_USE_BOTH);
        return $this;
    }

    public function header(string $key, string $value): self
    {
        return $this->setHeader($key, $value);
    }

    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function send(): self
    {
        if ($this->sent) {
            return $this;
        }

        http_response_code($this->statusCode);

        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }

        echo $this->content;
        $this->sent = true;

        return $this;
    }

    public function isSent(): bool
    {
        return $this->sent;
    }

    // Static factory methods

    public static function json(mixed $data, int $statusCode = 200, array $headers = []): self
    {
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        return new self(
            json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $statusCode,
            $headers
        );
    }

    public static function redirect(string $url, int $statusCode = 302, array $headers = []): self
    {
        $headers['Location'] = $url;
        return new self('', $statusCode, $headers);
    }

    public static function back(int $statusCode = 302): self
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        return self::redirect($referer, $statusCode);
    }

    public static function view(string $template, array $data = [], int $statusCode = 200, array $headers = []): self
    {
        $content = View::render($template, $data);
        return new self($content, $statusCode, $headers);
    }

    public static function download(string $path, string $name = null, array $headers = []): self
    {
        if (!file_exists($path)) {
            abort(404);
        }

        $name ??= basename($path);
        $mime = mime_content_type($path);

        $headers = array_merge([
            'Content-Type' => $mime,
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
            'Content-Length' => filesize($path),
            'Cache-Control' => 'no-cache, private',
            'Pragma' => 'no-cache',
        ], $headers);

        $response = new self('', 200, $headers);
        $response->sendHeadersOnly();

        readfile($path);
        return $response;
    }

    public static function stream(callable $callback, int $statusCode = 200, array $headers = []): self
    {
        $headers = array_merge([
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ], $headers);

        $response = new self('', $statusCode, $headers);
        $response->sendHeadersOnly();

        $callback($response);
        return $response;
    }

    public static function file(string $path, array $headers = []): self
    {
        if (!file_exists($path)) {
            abort(404);
        }

        $mime = mime_content_type($path);
        $etag = md5_file($path);
        $lastModified = gmdate('D, d M Y H:i:s', filemtime($path)) . ' GMT';

        $headers = array_merge([
            'Content-Type' => $mime,
            'ETag' => $etag,
            'Last-Modified' => $lastModified,
            'Cache-Control' => 'public, max-age=31536000',
        ], $headers);

        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag) {
            return new self('', 304, ['ETag' => $etag]);
        }

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] === $lastModified) {
            return new self('', 304, ['Last-Modified' => $lastModified]);
        }

        return new self('', 200, $headers)->sendFile($path);
    }

    private function sendHeadersOnly(): void
    {
        if ($this->sent) return;
        http_response_code($this->statusCode);
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }
        $this->sent = true;
    }

    private function sendFile(string $path): self
    {
        $this->sendHeadersOnly();
        readfile($path);
        return $this;
    }

    public static function noContent(int $statusCode = 204): self
    {
        return new self('', $statusCode);
    }

    public static function created(mixed $data = null, array $headers = []): self
    {
        return self::json($data, 201, $headers);
    }

    public static function accepted(mixed $data = null, array $headers = []): self
    {
        return self::json($data, 202, $headers);
    }

    public static function badRequest(string $message = 'Bad Request', array $headers = []): self
    {
        return self::json(['message' => $message], 400, $headers);
    }

    public static function unauthorized(string $message = 'Unauthorized', array $headers = []): self
    {
        return self::json(['message' => $message], 401, $headers);
    }

    public static function forbidden(string $message = 'Forbidden', array $headers = []): self
    {
        return self::json(['message' => $message], 403, $headers);
    }

    public static function notFound(string $message = 'Not Found', array $headers = []): self
    {
        return self::json(['message' => $message], 404, $headers);
    }

    public static function unprocessable(array $errors, array $headers = []): self
    {
        return self::json(['message' => 'Validation failed', 'errors' => $errors], 422, $headers);
    }

    public static function tooManyRequests(string $message = 'Too Many Requests', array $headers = []): self
    {
        return self::json(['message' => $message], 429, $headers);
    }

    public static function serverError(string $message = 'Internal Server Error', array $headers = []): self
    {
        return self::json(['message' => $message], 500, $headers);
    }

    public static function serviceUnavailable(string $message = 'Service Unavailable', array $headers = []): self
    {
        return self::json(['message' => $message], 503, $headers);
    }
}