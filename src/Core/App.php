<?php

declare(strict_types=1);

namespace Core;

use Throwable;

class App
{
    private static ?self $instance = null;
    private Router $router;
    private Request $request;
    private Response $response;
    private Database $database;
    private Session $session;
    private array $services = [];
    private array $booted = [];
    private string $basePath;
    private bool $maintenanceMode = false;

    private function __construct(string $basePath = '')
    {
        $this->basePath = $basePath ?: dirname(__DIR__, 3);

        error_reporting(E_ALL);
        ini_set('display_errors', config('app.debug') ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', $this->basePath . '/storage/logs/error.log');

        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleShutdown']);

        date_default_timezone_set(config('app.timezone', 'UTC'));
        mb_internal_encoding('UTF-8');

        $this->router = new Router();
        $this->request = Request::capture();
        $this->session = new Session();
    }

    public static function getInstance(string $basePath = ''): self
    {
        if (self::$instance === null) {
            self::$instance = new self($basePath);
        }
        return self::$instance;
    }

    public function run(): void
    {
        Session::init();

        if ($this->maintenanceMode && !is_admin()) {
            $this->renderMaintenance();
            return;
        }

        $response = $this->router->dispatch($this->request);
        $response->send();
    }

    public function handleException(Throwable $e): void
    {
        $code = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

        if (config('app.debug')) {
            $message = $e->getMessage() . "\n\n" . $e->getTraceAsString();
        } else {
            $message = match ($code) {
                404 => 'Halaman tidak ditemukan.',
                419 => 'Sesi telah berakhir. Silakan refresh halaman.',
                422 => 'Validasi gagal.',
                429 => 'Terlalu banyak permintaan. Coba lagi nanti.',
                default => 'Terjadi kesalahan server. Silakan coba lagi nanti.',
            };
        }

        if ($this->request->expectsJson()) {
            Response::json(['message' => $message], $code)->send();
        } else {
            Response::view('errors/' . $code, ['message' => $message, 'exception' => config('app.debug') ? $e : null], $code)->send();
        }
    }

    public function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            $this->handleException(new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            ));
        }
    }

    private function renderMaintenance(): void
    {
        http_response_code(503);
        if ($this->request->expectsJson()) {
            Response::serviceUnavailable('Sistem sedang dalam pemeliharaan.')->send();
        } else {
            Response::view('errors/maintenance', [], 503)->send();
        }
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getDatabase(): Database
    {
        if (!isset($this->services['database'])) {
            $this->services['database'] = Database::getInstance(config('database.connections.' . config('database.default')));
        }
        return $this->services['database'];
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? '/' . ltrim($path, '/') : '');
    }

    public function publicPath(string $path = ''): string
    {
        return $this->basePath . '/public' . ($path ? '/' . ltrim($path, '/') : '');
    }

    public function storagePath(string $path = ''): string
    {
        $path = $this->basePath . '/storage' . ($path ? '/' . ltrim($path, '/') : '');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return $path;
    }

    public function bind(string $abstract, mixed $concrete): void
    {
        $this->services[$abstract] = $concrete;
    }

    public function make(string $abstract): mixed
    {
        if (isset($this->services[$abstract])) {
            $service = $this->services[$abstract];
            return is_callable($service) ? $service($this) : $service;
        }
        if (class_exists($abstract)) {
            return new $abstract();
        }
        return null;
    }

    public function maintenance(bool $mode = true): void
    {
        $this->maintenanceMode = $mode;
    }

    public function isMaintenance(): bool
    {
        return $this->maintenanceMode;
    }

    public function version(): string
    {
        return '1.0.0';
    }
}

function base_path(string $path = ''): string
{
    return App::getInstance()->basePath($path);
}

function public_path(string $path = ''): string
{
    return App::getInstance()->publicPath($path);
}

function storage_path(string $path = ''): string
{
    return App::getInstance()->storagePath($path);
}

function app(string $abstract = null): mixed
{
    $instance = App::getInstance();
    return $abstract ? $instance->make($abstract) : $instance;
}