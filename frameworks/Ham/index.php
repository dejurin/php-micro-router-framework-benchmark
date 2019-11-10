<?php

require 'vendor/autoload.php';

$app = new Ham();

$app->route('/php-micro-router-framework-benchmark/frameworks/Ham/', function ($app) {
    return 'Hello world!';
});

$app->run();

require $_SERVER['DOCUMENT_ROOT'].'/php-micro-router-framework-benchmark/libs/output_data.php';
