<?php

require 'vendor/autoload.php';

Flight::route('/', function(){
    echo 'Hello World!';
});

Flight::start();

require $_SERVER['DOCUMENT_ROOT'].'/php-micro-router-framework-benchmark/libs/output_data.php';