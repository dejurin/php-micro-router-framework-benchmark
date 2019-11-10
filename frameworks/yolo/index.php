<?php

require 'vendor/autoload.php';

use function yolo\y;

yolo\yolisp(y(
    'yolo\yolo',
    y(
        'lambda',
        y('request'),
        y('new', YoLo\resPONsE::class, y('quote', "Hello world!"))
    )
));

require $_SERVER['DOCUMENT_ROOT'].'/php-micro-router-framework-benchmark/libs/output_data.php';
