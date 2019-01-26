<?php
require 'flight/Flight.php';

Flight::route('/', function(){
    echo 'Hello World!';
});

Flight::start();
