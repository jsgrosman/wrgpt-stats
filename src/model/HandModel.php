<?php
/**
 * Created by PhpStorm.
 * User: jgrosman
 * Date: 1/2/19
 * Time: 3:53 PM
 */

namespace wrgpt\model;

use wrgpt\util\DBConn;

class HandModel
{
    public $tournamentNum;

    public $tableName;

    public $handNum;

    public $player;

    public $latestRound;

    public $position;

    public $isAllIn = false;

    public $wasInShowdown = false;

    public $isWinner = false;

    public $putMoneyPreflop = false;

    public $raisedPreflop = false;

    public $cards;

    public function __construct($player, $tournamentNum, $tableName, $handNum, $position)
    {
        $this->tournamentNum = $tournamentNum;
        $this->player = $player;
        $this->tableName = $tableName;
        $this->handNum = $handNum;
        $this->position = $position;
    }

    public function save()
    {
        $conn = DBConn::getConnection();

        $insertSql =<<<SQL
         INSERT INTO hand_by_hand
          (tournament_id, table_name, hand_num, player, position, latest_round, cards, put_money_preflop, raised_preflop, is_all_in, was_in_showdown, is_winner) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE player = ?,
                                    position = ?, 
                                    latest_round = ?, 
                                    cards = ?, 
                                    put_money_preflop = ?, 
                                    raised_preflop = ?, 
                                    is_all_in = ?, 
                                    was_in_showdown = ?, 
                                    is_winner = ?
SQL;

        try {
            $conn ->execute($insertSql, [
                $this->tournamentNum,
                $this->tableName,
                $this->handNum,
                $this->player,
                $this->position,
                $this->latestRound,
                $this->cards,
                $this->putMoneyPreflop ? 1 : 0,
                $this->raisedPreflop ? 1 : 0,
                $this->isAllIn ? 1 : 0,
                $this->wasInShowdown ? 1 : 0,
                $this->isWinner ? 1 : 0,
                // update
                $this->player,
                $this->position,
                $this->latestRound,
                $this->cards,
                $this->putMoneyPreflop ? 1 : 0,
                $this->raisedPreflop ? 1 : 0,
                $this->isAllIn ? 1 : 0,
                $this->wasInShowdown ? 1 : 0,
                $this->isWinner ? 1 : 0,
            ]);
        }
        catch (\Exception $e) {
            print $e;
        }

    }

    }



