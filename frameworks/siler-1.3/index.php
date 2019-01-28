<?php

require 'vendor/autoload.php';

use Siler\Route;

Route\get('/', function () {
    echo 'Hello world!';
});

require $_SERVER['DOCUMENT_ROOT'].'/php-micro-router-framework-benchmark/libs/output_data.php';
