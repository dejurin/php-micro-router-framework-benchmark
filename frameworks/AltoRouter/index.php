<?php

require 'vendor/autoload.php';

$router = new AltoRouter();

$router->setBasePath(ltrim($_SERVER['REQUEST_URI'], '/'));
$router->map('GET|POST', '/', function () {
    echo 'Hello world!';
}, 'home');

// match current request
$match = $router->match();

// call closure or throw 404 status
if ($match && is_callable($match['target'])) {
    call_user_func_array($match['target'], $match['params']);
} else {
    // no route was matched
    header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
}
require $_SERVER['DOCUMENT_ROOT'].'/php-micro-router-framework-benchmark/libs/output_data.php';
