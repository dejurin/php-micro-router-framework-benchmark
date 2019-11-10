<?php

require 'mu.php';

echo (new µ)->get('/', function ($app) {
    return "Hello world!";
})->run();

require $_SERVER['DOCUMENT_ROOT'].'/php-micro-router-framework-benchmark/libs/output_data.php';
