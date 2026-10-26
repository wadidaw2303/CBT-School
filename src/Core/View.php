<?php

declare(strict_types=1);

namespace Core;

class View
{
    private static array $sharedData = [];
    private static array $composers = [];
    private static string $viewsPath = '';
    private static string $layout = '';

    public static function init(string $viewsPath = ''): void
    {
        self::$viewsPath = $viewsPath ?: base_path('templates');
    }

    public static function share(string $key, mixed $value): void
    {
        self::$sharedData[$key] = $value;
    }

    public static function composer(string $view, callable $callback): void
    {
        self::$composers[$view] = $callback;
    }

    public static function render(string $template, array $data = []): string
    {
        $data = array_merge(self::$sharedData, $data);

        if (isset(self::$composers[$template])) {
            self::$composers[$template]($data);
        }

        if (self::$layout) {
            $data['__layout'] = self::$layout;
            $content = self::renderTemplate($template, $data);
            self::$layout = '';
            return self::renderTemplate(self::$layout, array_merge($data, ['content' => $content]));
        }

        return self::renderTemplate($template, $data);
    }

    public static function layout(string $layout): void
    {
        self::$layout = $layout;
    }

    private static function renderTemplate(string $template, array $data): string
    {
        $path = self::resolvePath($template);

        if (!file_exists($path)) {
            throw new \RuntimeException("View not found: {$template} at {$path}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $path;
        return ob_get_clean();
    }

    private static function resolvePath(string $template): string
    {
        if (str_contains($template, '/')) {
            $path = self::$viewsPath . '/' . $template . '.php';
        } else {
            $path = self::$viewsPath . '/' . str_replace('.', '/', $template) . '.php';
        }

        return $path;
    }

    public static function exists(string $template): bool
    {
        return file_exists(self::resolvePath($template));
    }

    public static function component(string $name, array $data = []): string
    {
        return self::render('partials/' . $name, $data);
    }

    public static function include(string $template, array $data = []): string
    {
        return self::render($template, $data);
    }
}