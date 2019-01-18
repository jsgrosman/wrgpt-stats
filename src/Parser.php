<?php
/**
 * Created by PhpStorm.
 * User: jgrosman
 * Date: 1/2/19
 * Time: 11:43 AM
 */

namespace wrgpt;

use NumberFormatter;
use wrgpt\model\HandModel;
use wrgpt\model\TurnModel;

class Parser
{
    const START_LINE = '! History of this hand:';
    const END_LINE = '! Hand over';
    const SPECIAL_ACTIONS = [
        'Dealing a new hand',
        'Everyone antes',
        'No ante',
        'Pot right',
        '2 players',
        '3 players',
        '4 players',
        '5 players',
        '6 players',
        '7 players',
        '8 players',
        '9 players',
        '10 players',
        'Flopped card',
        'Player leaving table',
        'New player added to the table',
    ];

    public function parseHand($url)
    {
        $fileContents = file_get_contents($url);

        $lineByLine = explode("\n",$fileContents);

        $blindBet = null;
        $tableName = null;
        $handNum = null;
        $tournamentNum = null;
        $round = 'preflop';
        $previousTime = null;
        $previousRaise = false;
        $position = 1;
        $turnId = 1;

        $handModels = [];

        $inHandHistory = false;
        foreach ($lineByLine as $line)
        {
            if (strpos($line, '! Table') === 0)
            {
                $tableTokens = explode(',', $line);
                $tableName = explode(' ', $tableTokens[0])[2];
                $handNum = explode(' ', $tableTokens[1])[2];

                print $tableName . "," . $handNum . "\n";
            }

            if (strpos($line, Parser::START_LINE) === 0)
            {
                $inHandHistory = true;
                continue;
            }

            if (strpos($line, Parser::END_LINE) === 0)
            {
                break;
            }

            if (empty($line))
            {
                continue;
            }

            if (!$inHandHistory)
            {
                continue;
            }

            $turn = $this->getTurn($line, $round, $tableName, $handNum, $turnId++, $previousTime, $blindBet, $previousRaise);
            if (!empty($turn->player)) {
                $previousTime = $turn->timeStamp;

                if ($turn->action === 'blinds')
                {
                    $blindBet = $turn->bet;
                }

                if ($turn->action === 'raises')
                {
                    $previousRaise = true;
                }
                $turn->save();

                if (array_key_exists($turn->player, $handModels))
                {
                    /** @var HandModel $handModel */
                    $handModel = $handModels[$turn->player];
                }
                else
                {
                    $handModel = new HandModel($turn->player, $turn->tournamentNum, $turn->tableName, $turn->handNum, $position++);
                    $handModels[$turn->player] = $handModel;
                }

                $handModel->latestRound = $turn->round;
                $handModel->isAllIn = $turn->isAllIn;

                if ($turn->round === 'preflop')
                {
                    switch ($turn->action) {
                        case 'raises':
                        case 'reraises':
                            $handModel->raisedPreflop = true;
                        case 'calls':
                            $handModel->putMoneyPreflop = true;
                            break;
                    }

                }


            }
            else if (!empty($turn->round)) {
                $round = $turn->round;
            }
        }

        $winnerName = $this->findWinner($lineByLine);
        if (!empty($winnerName)) {
            $handModels[$winnerName]->isWinner = true;
        }

        foreach ($handModels as $handModel)
        {
            $cardsAndShowdown = $this->getCards($lineByLine, $handModel->player);
            if (!empty($cardsAndShowdown))
            {
                $handModel->wasInShowdown = $cardsAndShowdown[1];
                $handModel->cards = $cardsAndShowdown[0];
            }
            $handModel->save();
        }
    }

    private function getTurn($line, $round, $tableName, $handNum, $turnId, $previousTime, $blindBet, $previousRaise)
    {
        $lineTokens = explode('! ', $line);
        if (count($lineTokens) != 3)
        {
            return null;
        }
        list($ignore, $timeDateStr, $action) = $lineTokens;

        $phpDateArray = date_parse($timeDateStr);
        $phpDate = strtotime($timeDateStr);



        foreach (Parser::SPECIAL_ACTIONS as $specialAction)
        {
            if (strpos($action, $specialAction) === 0)
            {
                if ($specialAction === 'Pot right')
                {
                    switch ($round) {
                        case 'preflop' :
                            $round = 'flop';
                            break;
                        case 'flop' :
                            $round = 'turn';
                            break;
                        case 'turn' :
                            $round = 'river';
                            break;
                    }
                }
                $turn = new TurnModel();
                $turn->round = $round;
                return $turn;
            }
        }

        $bet = $this->getBet($action);

        list ($playerName, $playerAction) = $this->getPlayerAndAction($action, $previousRaise);

        $allIn = strpos($action, 'all in') > 0;


        $multiplier = $this->getBetMultiplier($bet, $blindBet);


        $turn = new TurnModel();
            $turn->tournamentNum = $this->getTournamentNumber($phpDate);
        $turn->tableName = $tableName;
        $turn->handNum = $handNum;
        $turn->turnId = $turnId;
        $turn->player = $playerName;
        $turn->timeStamp = date('Y-m-d H:i:s', $phpDate);
        $turn->hourOfDay = $phpDateArray['hour'];
        $turn->round = $round;
        $turn->action = $playerAction;
        $turn->bet = $bet;
        $turn->isAdvancedAction = ($turn->timeStamp == $previousTime);
        $turn->isAllIn = $allIn;
        $turn->bigBlind = $blindBet;
        $turn->multiplier = $multiplier;

        return $turn;
    }


    private function getBet($actionLine)
    {
        $bet = null;
        $betCount = preg_match('/\$\d*\b/' , $actionLine, $betMatches);
        if ($betCount > 0)
        {
            $bet = $betMatches[0];
        }

        return $bet;
    }

    private function getBetMultiplier($bet, $bigBlind)
    {
        $multiplier = null;
        if (!empty($bet) && !empty($bigBlind)) {

            $fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
            $currency = 'USD';

            $betValue = $fmt->parseCurrency($bet, $currency);
            $blindValue =  $fmt->parseCurrency($bigBlind, $currency);

            $multiplier = number_format($betValue / $blindValue, 2);
        }

        return $multiplier;
    }

    private function getPlayerAndAction($actionLine, $previousRaise = false)
    {
        $possibleActions =
            [
                'blinds',
                'folds',
                'calls',
                'raises',
                'bets',
                'is',
                'checks',
            ];

        $actionTokens = explode(' ', $actionLine);

        $playerName = '';
        foreach ($actionTokens as $token)
        {
            if (!in_array($token, $possibleActions))
            {
                if (!empty($playerName))
                {
                    $playerName .= ' ';
                }
                $playerName .= $token;
            }
            else
            {

                if ($token === 'is' && strpos($actionLine, 'vacation and folds') > 0)
                {
                    $playerAction = "folds (vacation)";
                }
                else if ($token === 'is' && strpos($actionLine, 'vacation and checks') > 0)
                {
                    $playerAction = "checks (vacation)";
                }
                else if ($token === 'is' && strpos($actionLine, 'back from vacation') > 0)
                {
                    $playerAction = "back (vacation)";
                }
                else if ($token === 'is' ) // player has 'is' in their name.. oops
                {
                    $playerName .= " " . $token;
                    continue;
                }
                else if ($token === 'raises' && $previousRaise)
                {
                    $playerAction = "reraises";
                }
                else
                    {
                    $playerAction = $token;
                }
                break;
            }
        }

        return [$playerName, $playerAction];
    }

    private function findWinner($handTxt)
    {
        foreach ($handTxt as $line)
        {
            $winsPos = strpos($line, 'wins $');
            if ($winsPos > 0)
            {
                $winner = substr($line, 2, $winsPos - 3);

                // handle multipots. Just pick the first winner
                if (strpos($winner, ":") > 0) {
                    $winner = trim(explode(':', $winner)[1]);
                }
                return $winner;
            }
        }

        return null;
    }

    private function getCards($handTxt, $playerName)
    {
        foreach ($handTxt as $line)
        {
            $playerNamePos = strpos($line, $playerName);
            $cardsPos = strpos($line, 'has: ');

            if ($playerNamePos > 0 && $cardsPos > 0)
            {
                return array(trim(substr($line, $cardsPos + 5)), true);
            }

            $cardsPos = strpos($line, 'reveals  ');
            if ($playerNamePos > 0 && $cardsPos > 0)
            {
                return array(trim(substr($line, $cardsPos + 9)), false);
            }

        }

        return null;
    }

    private function getTournamentNumber($handDate)
    {
        $currentTournament = 28;
        for ($year = 2018; $year >= 2008; $year--)
        {
            if ($handDate > strtotime($year . "-09-01"))
            {
                return $currentTournament;
            }
            $currentTournament--;
        }

        return null;
    }
}
