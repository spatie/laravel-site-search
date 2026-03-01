<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

if ($uri === '/booted') {
    echo 'app has booted';
    return;
}

$configFile = __DIR__ . '/config.json';

if (! file_exists($configFile)) {
    http_response_code(404);
    return;
}

$config = json_decode(file_get_contents($configFile), true);
$routesFile = __DIR__ . "/routeFiles/{$config['routes']}.php";

$routes = require $routesFile;

if (isset($routes[$uri])) {
    $route = $routes[$uri];

    if (isset($route['headers'])) {
        foreach ($route['headers'] as $name => $value) {
            header("{$name}: {$value}");
        }
    }

    if (isset($route['view'])) {
        echo file_get_contents(__DIR__ . '/../resources/views/' . $route['view']);
    } else {
        echo $route['body'];
    }

    return;
}

http_response_code(404);
