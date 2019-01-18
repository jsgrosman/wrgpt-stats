<?php
/**
 * Created by PhpStorm.
 * User: jgrosman
 * Date: 1/2/19
 * Time: 3:53 PM
 */

namespace wrgpt\model;

use wrgpt\util\DBConn;

class TurnModel
{
    public $tournamentNum;

    public $tableName;

    public $handNum;

    public $turnId;

    public $bigBlind;

    public $player;

    public $round;

    public $timeStamp;

    public $hourOfDay;

    public $action;

    public $bet;

    public $multiplier;

    public $isAllIn;

    public $isAdvancedAction;


    public function save()
    {
        $conn = DBConn::getConnection();

        $insertSql =<<<SQL
         INSERT INTO turn_by_turn
          (tournament_id, table_name, hand_num, turn_id, player, round, timestamp, hour_of_day, action, bet, big_blind, multiplier, is_all_in, is_advanced_action) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
          ON DUPLICATE KEY UPDATE player = ?,
                                  round = ?,
                                  timestamp = ?,
                                  hour_of_day = ?, 
                                  action = ?,
                                  bet = ?,
                                  big_blind = ?,
                                  multiplier = ?,
                                  is_all_in = ?, 
                                  is_advanced_action = ?
SQL;

        try {
            $conn ->execute($insertSql, [
                $this->tournamentNum,
                $this->tableName,
                $this->handNum,
                $this->turnId,
                $this->player,
                $this->round,
                $this->timeStamp,
                $this->hourOfDay,
                $this->action,
                $this->bet,
                $this->bigBlind,
                $this->multiplier,
                $this->isAllIn ? 1 : 0,
                $this->isAdvancedAction ? 1 : 0,
                // update
                $this->player,
                $this->round,
                $this->timeStamp,
                $this->hourOfDay,
                $this->action,
                $this->bet,
                $this->bigBlind,
                $this->multiplier,
                $this->isAllIn ? 1 : 0,
                $this->isAdvancedAction ? 1 : 0
            ]);
        }
        catch (\Exception $e) {
            print $e;
        }

    }
}


