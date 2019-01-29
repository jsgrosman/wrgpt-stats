<?php
/**
 * Created by PhpStorm.
 * User: jgrosman
 * Date: 1/4/19
 * Time: 4:51 PM
 */

namespace wrgpt\model;

use wrgpt\util\DBConn;

class PlayerModel
{
    private $player;

    public function __construct($player)
    {
        $this->player = $player;
    }

    public function getHours()
    {
        $conn = DBConn::getConnection();

        $sql =<<<SQL
select hour_of_day, count(*) as hourCount from turn_by_turn 
where player = ?
and is_advanced_action = ?
group by hour_of_day
order by hour_of_day;


SQL;

        $ret = [];
        for ($i = 0; $i <= 23; $i++)
        {
            $ret[$i . ':00'] = 0;
        }

        $result = $conn->queryFetchAll($sql, [$this->player, 0]);

        $count = 0;
        foreach ($result as $hourResult)
        {
            $value = $hourResult['hourCount'];
            $count += intval($value);
        }

        $countOfActions = array_reduce($result, function($sum, $val) { return $sum + $val['hourCount'];});
        foreach ($result as $hourResult)
        {
            $key = ($hourResult['hour_of_day'] + 3) % 24; // convert to EST
            $value = $hourResult['hourCount'];

            $count += intval($value);
            $ret[$key . ':00'] = floatval(number_format((intval($value) / $countOfActions) * 100, 2));
        }

        return $ret;

    }

    public function getAllActions()
    {
        $conn = DBConn::getConnection();

        $sql =<<<SQL
select action, count(action) as actionCount from turn_by_turn
where player = ?
group by action
order by action
SQL;

        $result = $conn->queryFetchAll($sql, [$this->player]);

        $ret = [
            'calls' => 0,
            'bets' => 0,
            'raises' => 0,
            'reraises' => 0,
            'folds' => 0,
            'checks' => 0
        ];
        foreach ($result as $actionResult)
        {
            $key = $actionResult['action'];
            $value = $actionResult['actionCount'];
            $ret[$key] = intval($value);
        }

        return $ret;
    }

    public function getActionsByRound($round)
    {
        $conn = DBConn::getConnection();

        $sql =<<<SQL
select action, count(action) as actionCount from turn_by_turn
where player = ?
and round = ?
group by action
order by action
SQL;

        $result = $conn->queryFetchAll($sql, [$this->player, $round]);
        $ret = [
            'calls' => 0,
            'bets' => 0,
            'raises' => 0,
            'reraises' => 0,
            'folds' => 0,
            'checks' => 0
        ];
        foreach ($result as $actionResult)
        {
            $key = $actionResult['action'];
            $value = $actionResult['actionCount'];
            $ret[$key] = intval($value);
        }

        return $ret;
    }

    public function getAllTables()
    {
        $conn = DBConn::getConnection();

        $sql =<<<SQL
select distinct tournament_id, table_name from hand_by_hand
where player = ?
SQL;

        $result = $conn->queryFetchAll($sql, [$this->player]);
        return array_map(function($r) { return $r['tournament_id'] . "-" . $r['table_name'];}, $result);
    }

    public function getAllTournaments()
    {
        $conn = DBConn::getConnection();

        $sql =<<<SQL
select distinct tournament_id from hand_by_hand
where player = ?
SQL;

        $result = $conn->queryFetchAll($sql, [$this->player]);
        return array_map(function($r) { return $r['tournament_id'];}, $result);
    }

    public function getFirstAndLastDate()
    {
        $conn = DBConn::getConnection();

        $sql =<<<SQL
select min(timestamp) as first, max(timestamp) as last from turn_by_turn
where player = ?
SQL;

        return $result = $conn->queryFetchAll($sql, [$this->player])[0];
    }

    public function getMultiplierInfo()
    {
        $conn = DBConn::getConnection();

        $sql =<<<SQL
select round(min(multiplier), 2) as minMult, round(max(multiplier), 2) as maxMult, round(avg(multiplier), 2) as avgMult from turn_by_turn
where player = ?
and round = ?
and action = ?
SQL;

        return $result = $conn->queryFetchAll($sql, [$this->player, 'preflop', 'raises'])[0];
    }

    public function getRoundCounts()
    {
        $conn = DBConn::getConnection();

        $sql =<<<SQL
select round, count(distinct table_name, hand_num) as roundCount
from turn_by_turn
where player = ?
group by round
SQL;

        $result = $conn->queryFetchAll($sql, [$this->player]);

        $ret = [];
        foreach ($result as $r)
        {
            $key = $r['round'];
            $value = $r['roundCount'];
            $ret[$key] = intval($value);
        }

        return $ret;
    }

    public function getVPIPAndPFR()
    {
        $conn = DBConn::getConnection();

        $sql =<<<SQL
select count(v1) as vpip_yes, count(v2) as vpip_no, count(r1) as pfr_yes, count(r2) as pfr_no
from (
  SELECT
    CASE WHEN put_money_preflop = 1 THEN 1 END v1,
    CASE WHEN put_money_preflop = 0 THEN 1 END v2,
    CASE WHEN raised_preflop = 1 THEN 1 END r1,
    CASE WHEN raised_preflop = 0 THEN 1 END r2
  FROM hand_by_hand
  where player = ?
  ) hand_by_hand
SQL;

        $result = $conn->queryFetchAll($sql, [$this->player])[0] ;

        $vpip_yes = $result['vpip_yes'];
        $vpip_no = $result['vpip_no'];
        $pfr_yes = $result['pfr_yes'];
        $pfr_no = $result['pfr_no'];

        $vpip_raw = ($vpip_yes/($vpip_yes + $vpip_no) * 100);
        $pfr_raw = ($pfr_yes/($pfr_yes + $pfr_no) * 100);


        $vpip = floatval(number_format($vpip_raw, 2));
        $pfr = floatval(number_format($pfr_raw, 2));

        if ($vpip > 40)
        {
            $classification = 'super loose';
        }
        else if ($vpip > 31)
        {
            $classification = 'very loose';
        }
        else if ($vpip > 21)
        {
            $classification = 'loose';
        }
        else if ($vpip > 11)
        {
            $classification = 'tight';
        }
        else
        {
            $classification = 'very tight';
        }

        $range = $this->getRangeForVPIP($vpip);

        if ($pfr != 0 && $pfr/$vpip > .70)
        {
            $classification .= ' aggressive';
        }
        else
        {
            $classification .= ' passive';
        }





        return [$vpip, $pfr, $classification, $range];
    }

    public function getVPIPAndPFRForPosition($position)
    {
        $conn = DBConn::getConnection();

        $sql =<<<SQL
select count(v1) as vpip_yes, count(v2) as vpip_no, count(r1) as pfr_yes, count(r2) as pfr_no
from (
  SELECT
    CASE WHEN put_money_preflop = 1 THEN 1 END v1,
    CASE WHEN put_money_preflop = 0 THEN 1 END v2,
    CASE WHEN raised_preflop = 1 THEN 1 END r1,
    CASE WHEN raised_preflop = 0 THEN 1 END r2
  FROM hand_by_hand
  where player = ?
  and position = ?
  ) hand_by_hand
SQL;

        $result = $conn->queryFetchAll($sql, [$this->player, $position])[0] ;

        $vpip_yes = $result['vpip_yes'];
        $vpip_no = $result['vpip_no'];
        $pfr_yes = $result['pfr_yes'];
        $pfr_no = $result['pfr_no'];

        $vpip_raw = ($vpip_yes/($vpip_yes + $vpip_no) * 100);
        $pfr_raw = ($pfr_yes/($pfr_yes + $pfr_no) * 100);


        $vpip = number_format($vpip_raw, 2);
        $pfr = number_format($pfr_raw, 2);

        if ($vpip > 40)
        {
            $classification = 'super loose';
        }
        else if ($vpip > 31)
        {
            $classification = 'very loose';
        }
        else if ($vpip > 21)
        {
            $classification = 'loose';
        }
        else if ($vpip > 11)
        {
            $classification = 'tight';
        }
        else
        {
            $classification = 'very tight';
        }

        $range = $this->getRangeForVPIP($vpip);

        if ($pfr != 0 && $pfr/$vpip > .70)
        {
            $classification .= ' aggressive';
        }
        else
        {
            $classification .= ' passive';
        }


        return [$vpip, $pfr, $classification, $range];
    }

    public function getPercentageToFlop()
    {
        $conn = DBConn::getConnection();

        $sql =<<<SQL
select round, count(distinct table_name, hand_num) as roundCount
from turn_by_turn
where player = ?
group by round
SQL;

        $preflopCount = 0;
        $flopCount = 0;
        $result = $conn->queryFetchAll($sql, [$this->player]) ;
        foreach ($result as $r)
        {
            if ($r['round'] == 'preflop') {
                $preflopCount = $r['roundCount'];
            }

            if ($r['round'] == 'flop') {
                $flopCount = $r['roundCount'];
            }
        }

        return number_format(($flopCount/$preflopCount * 100), 2) . '%';

    }


    public function getShownCards()
    {
        $conn = DBConn::getConnection();


        $sql =<<<SQL
select cards, is_winner, table_name, hand_num
from hand_by_hand
where player = ?
and cards is not null
SQL;

        $result = $conn->queryFetchAll($sql, [$this->player]);
        return array_map(function($r) {
            $tableName = $r['table_name'];
            $handNum = $r['hand_num'];

            $round = substr($tableName,0, 1);
                $url = "http://hands.wrgpt.org/${round}/hands/${tableName}_${handNum}.txt";

            if ($r['is_winner'] == 1) {
                return "<a href=\"$url\">*" . $r['cards'] . "*</a>";
            }
            else {
                return "<a href=\"$url\">" . $r['cards'] . "</a>";
            }
        }, $result);
    }

    public function getShownCardsWithoutLinks()
    {
        $conn = DBConn::getConnection();


        $sql =<<<SQL
select cards
from hand_by_hand
where player = ?
and cards is not null
SQL;

        $result = $conn->queryFetchAll($sql, [$this->player]);
        return array_map(function($r) {
            return $r['cards'];
        }, $result);
    }

    public function getPercentageToShowdown()
    {
        $conn = DBConn::getConnection();


        $sql =<<<SQL
select count(*) as showdownCount
from hand_by_hand
where player = ?
and was_in_showdown = ?
SQL;

        $showdownResult = $conn->queryFetchAll($sql, [$this->player, 1]);
        $showdownCount = $showdownResult[0]['showdownCount'];

        $sql =<<<SQL
select count(*) as flopCount
from hand_by_hand
where player = ?
and latest_round != ?
SQL;

        $flopResult = $conn->queryFetchAll($sql, [$this->player, 'preflop']);
        $flopCount = $flopResult[0]['flopCount'];

        if ($flopCount > 0) {
            return number_format(($showdownCount / $flopCount * 100), 2) . '%';
        }
        else {
            return '0%';
        }

    }

    public function getChips()
    {
        $conn = DBConn::getConnection();

        $sql =<<<SQL
select chips
from hand_by_hand
where player = ?
and tournament_id = ?
order by hand_began
SQL;

        $result = $conn->queryFetchAll($sql, [$this->player, 28]);
        return array_map(function($r) { return intval($r['chips']);}, $result);
    }

    protected function getRangeForVPIP($vpip)
    {
        switch (true) {
            case $vpip <= 5:
                $range = '88+, ATs+, KQs, AKo';
                break;

            case $vpip <= 10:
                $range = '77+, A9s+, KTs+, QTs+, AJo+';
                break;

            case $vpip <= 15:
                $range = '77+, A7s+, K9s+, QTs+, JTs, ATo+, KTo+, QJo';
                break;

            case $vpip <= 20:
                $range = '66+, A4s+, K7s+, Q9s+, J9s+, T9s, A9o+, KTo+, QTo+, JTo';
                break;

            case $vpip <= 25:
                $range = '66+, A2s+, K6s+, Q8s+, J8s+, T8s+, A7o+, K9o+, QTo+, JTo';
                break;

            case $vpip <= 35:
                $range = '55+, A2s+, K3s+, Q5s+, JTs+, T7s+, 97s+, 87s, A4o+, K8o+, Q9o+, J9o+, T9o';
                break;

            case $vpip <= 50:
                $range = '33+, A2s+, K2s+, Q2s+, J4s+, T6s+, 96s+, 86s+, 76s+, 65s+, A2o+, K5o+, Q7o+, J7o+, T7o+, 98o';
                break;

            case $vpip <= 75:
                $range = '22+, A2s+, K2s+, Q2s+, J2s+, T2s+, 92s+, 83s+, 73s+, 63s+, 52s+, 43s, A2o+, K2o+, Q2o+, J4o+, T6o, 96o+, 86o+, 75o+, 65o';
                break;

            default:
                $range = 'any';
                break;
        }

        return $range;
    }

    public static function getAllPlayers()
    {
        $conn = DBConn::getConnection();

        $sql =<<<SQL
    SELECT distinct player FROM turn_by_turn order by player

SQL;
        $results = $conn->queryFetchAll($sql);
        return array_map(function($r) { return $r['player']; }, $results);

    }

}
