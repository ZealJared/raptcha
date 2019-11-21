<?php

class Route
{
  /** @var callable[] */
  private static $routes = [];

  public static function add(string $route, callable $handler): void
  {
    self::$routes[$route] = $handler;
  }

  public static function exists(string $route): bool
  {
    return isset(self::$routes[$route]) && is_callable(self::$routes[$route]);
  }

  public static function execute(string $route): object
  {
    return self::$routes[$route]();
  }
}
