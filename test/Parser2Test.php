<?php
require '../vendor/autoload.php';

use wrgpt\Parser2;

class Parser2Test extends PHPUnit_Framework_TestCase
{

    public function testGetTournamentNumber2019()
    {
        $date = '02/20/19 14:33';
        $phpDate = strtotime($date);

        $parser = new Parser2();
        $tournament = $parser->getTournamentNumber($phpDate);

        $this->assertEquals(28, $tournament);
    }

    public function testGetTournamentNumber2018AfterSep()
    {
        $date = '12/20/18 14:33';
        $phpDate = strtotime($date);

        $parser = new Parser2();
        $tournament = $parser->getTournamentNumber($phpDate);

        $this->assertEquals(28, $tournament);
    }

    public function testGetTournamentNumber2018BeforeSep()
    {
        $date = '7/20/18 14:33';
        $phpDate = strtotime($date);

        $parser = new Parser2();
        $tournament = $parser->getTournamentNumber($phpDate);

        $this->assertEquals(27, $tournament);
    }

    public function testGetTournamentNumber2010()
    {
        $date = '10/20/10 14:33';
        $phpDate = strtotime($date);

        $parser = new Parser2();
        $tournament = $parser->getTournamentNumber($phpDate);

        $this->assertEquals(20, $tournament);
    }

    public function testGetTournamentLongAgo()
    {
        $date = '10/20/01 14:33';
        $phpDate = strtotime($date);

        $parser = new Parser2();
        $tournament = $parser->getTournamentNumber($phpDate);

        $this->assertNull($tournament);
    }
}
