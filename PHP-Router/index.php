<?php
require __DIR__.'/vendor/autoload.php';

use PHPRouter\RouteCollection;
use PHPRouter\Router;
use PHPRouter\Route;

final class SomeController
{
    public function indexAction()
    {
        echo "Hello World!";
    }
}

$collection = new RouteCollection();
$collection->attachRoute(new Route('/', array(
    '_controller' => 'SomeController::indexAction',
    'methods' => 'GET'
)));

$router = new Router($collection);
$route = $router->matchCurrentRequest();

require $_SERVER['DOCUMENT_ROOT'].'/php-micro-router-framework-benchmark/libs/output_data.php';