<?php

namespace App\Support;

class TourRegistry
{
    /** @see modalRouteKey()  camelCase types e.g. addSiswa */
    private const MODAL_TYPE_PATTERN = '[a-zA-Z][a-zA-Z0-9]*';

    private static ?array $routes = null;

    private static ?array $sessionOverrides = null;

    public static function all(): array
    {
        if (self::$routes === null) {
            self::$routes = self::build();
        }

        return self::$routes;
    }

    public static function steps(?string $route): array
    {
        if (! $route) {
            return [];
        }

        $steps = self::all()[$route] ?? [];

        while ($steps !== []) {
            $element = $steps[0]['element'] ?? null;
            if (is_string($element) && str_contains($element, 'data-tour="nav-')) {
                array_shift($steps);

                continue;
            }

            break;
        }

        return $steps;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function pageSteps(?string $route): array
    {
        return array_values(array_filter(
            self::steps($route),
            fn (array $step) => empty($step['openModal'])
                && ! str_contains($step['element'] ?? '', 'data-tour="nav-'),
        ));
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function modalStepsByType(?string $route): array
    {
        if (! $route) {
            return [];
        }

        $grouped = [];

        foreach (self::steps($route) as $step) {
            $type = $step['openModal'] ?? null;
            if (! $type) {
                continue;
            }

            $grouped[$type][] = $step;
        }

        return $grouped;
    }

    public static function modalRouteKey(string $route, string $type): string
    {
        return "{$route}.modal.{$type}";
    }

    public static function has(?string $route): bool
    {
        if (! $route) {
            return false;
        }

        if (array_key_exists($route, self::all())) {
            return true;
        }

        if (preg_match('/^(.+)\.modal\.('.self::MODAL_TYPE_PATTERN.')$/', $route, $matches)) {
            [, $baseRoute, $type] = $matches;
            $modalSteps = self::modalStepsByType($baseRoute);

            return isset($modalSteps[$type]) && $modalSteps[$type] !== [];
        }

        return false;
    }

    public static function routes(): array
    {
        return array_keys(self::all());
    }

    public static function hubFor(?string $route): ?string
    {
        if (! $route || ! self::has($route)) {
            return null;
        }

        if (str_ends_with($route, '.index') && self::isHubRoute($route)) {
            return $route;
        }

        if (str_ends_with($route, '.show')) {
            $candidate = preg_replace('/\.show$/', '.index', $route);
            if ($candidate && self::isHubRoute($candidate)) {
                return $candidate;
            }
        }

        if (preg_match('/^(.+)\.modal\.'.self::MODAL_TYPE_PATTERN.'$/', $route, $matches)) {
            return self::hubFor($matches[1]);
        }

        return $route;
    }

    public static function isHubRoute(?string $route): bool
    {
        if (! $route || ! self::has($route)) {
            return false;
        }

        if (str_ends_with($route, '.index')) {
            return true;
        }

        return self::hubFor($route) === $route;
    }

    /**
     * @return array<int, string>
     */
    public static function showRoutesForHub(string $hub): array
    {
        $routes = self::sessionOverrides()[$hub]['show'] ?? [];

        $candidate = preg_replace('/\.index$/', '.show', $hub);
        if ($candidate && $candidate !== $hub && self::has($candidate)) {
            $routes[] = $candidate;
        }

        return array_values(array_unique($routes));
    }

    /**
     * @return array<int, string>
     */
    public static function modalTypesForHub(string $hub): array
    {
        return array_keys(self::modalStepsByType($hub));
    }

    public static function belongsToHub(?string $route, string $hub): bool
    {
        if (! $route) {
            return false;
        }

        if ($route === $hub) {
            return true;
        }

        if (self::hubFor($route) === $hub) {
            return true;
        }

        if (in_array($route, self::showRoutesForHub($hub), true)) {
            return true;
        }

        if (str_starts_with($route, "{$hub}.modal.")) {
            return true;
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public static function contextForRoute(?string $route): array
    {
        $hubRoute = self::hubFor($route);

        if (! $hubRoute) {
            return [
                'hubRoute' => null,
                'isHubPage' => false,
                'showRoutes' => [],
                'modalTypes' => [],
                'isShowPage' => false,
            ];
        }

        $showRoutes = self::showRoutesForHub($hubRoute);

        return [
            'hubRoute' => $hubRoute,
            'isHubPage' => $route === $hubRoute,
            'showRoutes' => $showRoutes,
            'modalTypes' => self::modalTypesForHub($hubRoute),
            'isShowPage' => in_array($route, $showRoutes, true),
        ];
    }

    private static function sessionOverrides(): array
    {
        if (self::$sessionOverrides === null) {
            self::$sessionOverrides = require config_path('tours/sessions.php');
        }

        return self::$sessionOverrides;
    }

    private static function build(): array
    {
        $index = require config_path('tours/index.php');
        $modals = require config_path('tours/modals.php');
        $show = require config_path('tours/show.php');
        $pages = require config_path('tours/pages.php');

        $merged = array_merge($index, $show, $pages);

        foreach ($modals as $route => $modalSteps) {
            if (isset($merged[$route])) {
                $merged[$route] = array_merge($merged[$route], $modalSteps);
            } else {
                $merged[$route] = $modalSteps;
            }
        }

        return $merged;
    }
}
