<?php

/**
 * @noinspection PhpUnusedParameterInspection
 */

use Dingo\Api\Routing\Router;

// Load Dingo API router
/** @var Router $api */
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace' => 'App\Http\Controllers',
    'middleware' => []
], function (Router $api) { // don't remove var $api
    /** @noinspection PhpIncludeInspection 默认路由用于强跳转及测试 */
    require_once base_path('routes/api') . '/default.php';

    $api->group(['namespace' => 'v1'], function (Router $api) {
        foreach (read_dir_queue(base_path('routes/api/v1'), 1) as $route) {
            require_once base_path('routes/api/v1') . "/{$route}";
        }
    });
});

//$api->version('v2', [
//    'namespace' => 'App\Http\Controllers',
//    'middleware' => []
//], function (Router $api) { // don't remove var $api
//    $api->group(['namespace' => 'v2'], function (Router $api) {
//        foreach (read_dir_queue(base_path('routes/api/v2'), 1) as $route) {
//            require_once base_path('routes/api/v2') . "/{$route}";
//        }
//    });
//});
