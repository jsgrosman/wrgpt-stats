<?php
/**
 * Created by PhpStorm.
 * User: jgrosman
 * Date: 2019-01-24
 * Time: 00:20
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use wrgpt\controller\PlayerController;

require '../../vendor/autoload.php';

error_log('rewritten!!!!');
error_log(print_r($_SERVER, true));

$app = new \Slim\App;
$app->get('/players/{name}', function (Request $request, Response $response, array $args) {
    error_log('/api/players/name');


    $name = $args['name'];
    $playerController = new PlayerController();
    $playerView = $playerController->getPlayer($name);

    return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode($playerView));
});

$app->get('/players', function (Request $request, Response $response, array $args) {
    $playerController = new PlayerController();
    $allPlayersView = $playerController->getAllPlayers();


    return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode($allPlayersView));
});
$app->run();
