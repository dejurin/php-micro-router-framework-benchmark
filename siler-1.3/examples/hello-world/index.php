<?php

use Siler\Route;

chdir(dirname(dirname(__DIR__)));

require 'vendor/autoload.php';

Route\get('/', function () {
    echo 'Hello world!';
});

require $_SERVER['DOCUMENT_ROOT'].'/php-micro-router-framework-benchmark/libs/output_data.php';