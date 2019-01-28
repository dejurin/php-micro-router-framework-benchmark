<?php

require 'vendor/autoload.php';

$f3 = \Base::instance();
$f3->route('GET /',
    function () {
        echo 'Hello world!';
    }
);
$f3->run();

require $_SERVER['DOCUMENT_ROOT'].'/php-micro-router-framework-benchmark/libs/output_data.php';
