<?php
/**
 * Created by PhpStorm.
 * User: jgrosman
 * Date: 2019-01-24
 * Time: 00:22
 */

namespace wrgpt\controller;

use wrgpt\model\PlayerModel;

class PlayerController
{

    public function getPlayer($playerName)
    {
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

        return $result;
    }

    public function getAllPlayers()
    {
        $players = PlayerModel::getAllPlayers();
        return $players;
    }

}
