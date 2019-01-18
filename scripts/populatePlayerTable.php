<?php
/**
 * Created by PhpStorm.
 * User: jgrosman
 * Date: 2019-01-17
 * Time: 16:05
 */
require_once "../vendor/autoload.php";

use wrgpt\model\PlayerModel;

$playerList = PlayerModel::getAllPlayers();

foreach ($playerList as $player)
{
    $playerModel = new PlayerModel($player);
    list($vpip, $pfr, $classification, $range) = $playerModel->getVPIPAndPFR();

    $cards = $playerModel->getShownCardsWithoutLinks();

    print $player . ":" . $vpip . ":" . $pfr . ':' . join(', ', $cards) . "\n";
}
