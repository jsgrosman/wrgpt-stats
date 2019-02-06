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
$app->get('/players/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $playerController = new PlayerController();
    $playerView = $playerController->getPlayer($name);

    return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode($playerView));
});

$app->get('/tournaments/{tournamentNum}/hands/{handName}', function (Request $request, Response $response, array $args) {
    $tournament = $args['tournamentNum'];
    $handName = $args['handName'];
    $round = substr($handName,0, 1);

    $filename = __DIR__ . "/../../data/${tournament}/${round}/${handName}.txt";
    $hand = file_get_contents($filename);

    return $response->withStatus(200)
        ->withHeader('Content-Type', 'text/plain')
        ->write($hand);
});

$app->get('/players', function (Request $request, Response $response, array $args) {
    $playerController = new PlayerController();
    $allPlayersView = $playerController->getAllPlayers();


    return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode($allPlayersView));
});
$app->run();
