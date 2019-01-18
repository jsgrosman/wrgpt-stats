<?php
/**
 * Created by PhpStorm.
 * User: jgrosman
 * Date: 1/4/19
 * Time: 4:51 PM
 */

require_once "../vendor/autoload.php";
use wrgpt\model\PlayerModel;

header('content-type: application/json');


$players = PlayerModel::getAllPlayers();


print json_encode($players);
