<?php

require_once '../common.php';

Route::add('POST /add_image', function () {
  return Challenge::saveImage();
});
Route::add('POST /handle_challenge', function () {
  return Challenge::handle();
});
Route::add('GET /get_challenge', function () {
  return Challenge::get();
});
Route::add('POST /check_challenge', function () {
  return Challenge::check();
});

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$_SERVER['PATH_INFO'] = $_SERVER['PATH_INFO'] ?? $_SERVER['REDIRECT_PATH_INFO'];
$path = preg_replace('~(.)/$~', '$1', $_SERVER['PATH_INFO'] ?? '/');
$route = sprintf('%s %s', $method, $path);
$return = null;
if (Route::exists($route)) {
  try {
    $return = Route::execute($route);
  } catch (Throwable $e) {
    $return = (object) [
      'error' => $e->getMessage(),
      'details' => $e->getTraceAsString(),
    ];
  }
} else {
  // 404
  $return = (object) [
    'error' => '404 - Page not found.',
    'details' => sprintf('No handler found for route: %s', $route),
  ];
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
die(json_encode($return, JSON_PRETTY_PRINT));
