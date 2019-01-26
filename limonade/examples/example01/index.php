<?php
require_once dirname(dirname(dirname(__FILE__))).'/lib/limonade.php';

dispatch('/', 'hello_world');
  function hello_world()
  {
    return "Hello world!";
  }

run();

require $_SERVER['DOCUMENT_ROOT'].'/php-micro-router-framework-benchmark/libs/output_data.php';