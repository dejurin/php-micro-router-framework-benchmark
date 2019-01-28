<?php

require 'vendor/autoload.php';

$hello = new Tipsy\Tipsy();
$hello->router()
    ->home(function () {
        echo 'Hello world!';
    })
    ->otherwise(function () {
        echo '404';
    });
$hello->run();

require $_SERVER['DOCUMENT_ROOT'].'/php-micro-router-framework-benchmark/libs/output_data.php';
