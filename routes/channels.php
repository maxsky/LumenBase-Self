<?php

use Illuminate\Broadcasting\BroadcastController;

/** @var Laravel\Lumen\Routing\Router $router */
$router->addRoute(
    ['GET', 'POST'], '/broadcasting/auth', '\\' . BroadcastController::class . '@authenticate'
);
