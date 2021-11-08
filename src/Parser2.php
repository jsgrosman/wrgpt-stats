<?php
/**
 * Created by PhpStorm.
 * User: jgrosman
 * Date: 2019-02-20
 * Time: 22:12
 */

namespace wrgpt;


class Parser2
{


    public function getCards($handTxt, $playerName)
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

    /**
     * Hand date is a Unix timestamp
     *
     * @param string $handDate
     * @return int|null
     */
    public function getTournamentNumber($handDate)
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
