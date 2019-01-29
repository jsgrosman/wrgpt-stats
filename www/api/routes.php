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

$app = new \Slim\App;
$app->get('api/players/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $playerController = new PlayerController();
    $playerView = $playerController->getPlayer($name);

    return json_encode($playerView);
});

$app->get('api/players', function (Request $request, Response $response, array $args) {
    $playerController = new PlayerController();
    $allPlayersView = $playerController->getAllPlayers();

    return json_encode($allPlayersView);
});
$app->run();
