<?php

declare(strict_types=1);

namespace Core;

class Config
{
    private array $config = [];
    private static ?self $instance = null;

    private function __construct(string $envPath = '')
    {
        $this->loadEnvironment($envPath);
        $this->loadConfigFiles();
    }

    public static function getInstance(string $envPath = ''): self
    {
        if (self::$instance === null) {
            self::$instance = new self($envPath);
        }
        return self::$instance;
    }

    private function loadEnvironment(string $envPath): void
    {
        $paths = [
            $envPath,
            __DIR__ . '/../../../.env',
            __DIR__ . '/../../../.env.local',
        ];

        foreach ($paths as $path) {
            if ($path && file_exists($path)) {
                $parsed = parse_ini_file($path, true, INI_SCANNER_TYPED);
                if ($parsed !== false) {
                    foreach ($parsed as $key => $value) {
                        if (is_array($value)) {
                            foreach ($value as $subKey => $subValue) {
                                $_ENV["{$key}_{$subKey}"] = $subValue;
                                putenv("{$key}_{$subKey}={$subValue}");
                            }
                        } else {
                            $_ENV[$key] = $value;
                            putenv("{$key}={$value}");
                        }
                    }
                }
                break;
            }
        }
    }

    private function loadConfigFiles(): void
    {
        $configDir = __DIR__ . '/../../config/';
        if (is_dir($configDir)) {
            foreach (glob($configDir . '*.php') as $file) {
                $key = basename($file, '.php');
                $this->config[$key] = require $file;
            }
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $target = &$this->config;

        foreach ($keys as $segment) {
            if (!is_array($target) || !array_key_exists($segment, $target)) {
                $target[$segment] = [];
            }
            $target = &$target[$segment];
        }

        $target = $value;
    }

    public function all(): array
    {
        return $this->config;
    }

    public function has(string $key): bool
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return false;
            }
            $value = $value[$segment];
        }

        return true;
    }
}