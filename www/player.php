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

$playerName = $_GET['player'];

$playerModel = new PlayerModel($playerName);
$firstAndLast = $playerModel->getFirstAndLastDate();
$multipliers = $playerModel->getMultiplierInfo();
$roundCounts = $playerModel->getRoundCounts();
$allActions = $playerModel->getAllActions();
$riverActions = $playerModel->getActionsByRound('river');
list($vpip, $pfr, $classification, $range) = $playerModel->getVPIPAndPFR();

$positionInfo = [];
for ($pos = 1; $pos < 10; $pos++)
{
    $positionInfo[$pos] = $playerModel->getVPIPAndPFRForPosition($pos);
}

if ($allActions['calls'] > 0) {
    $aggFactor = number_format(($allActions['bets'] + $allActions['raises'] + $allActions['reraises']) / ($allActions['calls']), 1);
}
else {
    $aggFactor = "1.0";
}

$result = [
    'name' => $playerName,
    'firstAction' => $firstAndLast['first'],
    'lastAction' => $firstAndLast['last'],
    'tournaments' => $playerModel->getAllTournaments(),
    'tables' => $playerModel->getAllTables(),
    'cards' => $playerModel->getShownCards(),
    'hours' => $playerModel->getHours(),
    'actions' => [
        'all' => $allActions,
        'preflop' => $playerModel->getActionsByRound('preflop'),
        'flop' => $playerModel->getActionsByRound('flop'),
        'turn' => $playerModel->getActionsByRound('turn'),
        'river' => $riverActions,
    ],
    'rounds' => $roundCounts,
    'multipliers' => $multipliers,
    'flopPct' => $playerModel->getPercentageToFlop(),
    'vpip' => $vpip . '%',
    'pfr' => $pfr . '%',
    'classification' => $classification,
    'range' => $range,
    'position' => [],
    'aggressionFactor' => $aggFactor,
    'wentToShowdownPct' => $playerModel->getPercentageToShowdown(),
];

foreach ($positionInfo as $pos => $info)
{
    $result['position'][$pos] = [
        'vpip' => $info[0] . '%',
        'pfr' => $info[1] . '%',
        'classification' => $info[2],
        'range' => $info[3],
    ];
}

print json_encode($result);
