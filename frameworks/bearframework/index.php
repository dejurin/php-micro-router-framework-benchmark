<?php

require 'vendor/autoload.php';

use BearFramework\App;

$app = new App();

$app->routes->add('/', function () {
    return new App\Response('Hello world!');
});

$app->run();

require $_SERVER['DOCUMENT_ROOT'].'/php-micro-router-framework-benchmark/libs/output_data.php';
