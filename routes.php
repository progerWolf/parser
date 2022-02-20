<?php

use Laminas\Diactoros\Response\JsonResponse;
use MiladRahimi\PhpRouter\Exceptions\InvalidCallableException;
use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use MiladRahimi\PhpRouter\Router;
use App\Http\Controllers\HomeController;

$router = Router::create();

$router->get('/', [HomeController::class, 'index']);

try {
    $router->dispatch();
} catch (RouteNotFoundException $e) {
    $router->getPublisher()->publish(new JsonResponse('Not found.', 404));
} catch (InvalidCallableException $e) {
}
