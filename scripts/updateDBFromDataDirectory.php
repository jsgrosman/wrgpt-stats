<?php
/**
 * Created by PhpStorm.
 * User: jgrosman
 * Date: 1/2/19
 * Time: 10:15 AM
 */
require_once "../vendor/autoload.php";

use wrgpt\Parser;

$dataDir = "../data";
$tournamentDirs = array_diff(scandir($dataDir), array('.', '..'));

foreach ($tournamentDirs as $tournamentDir) {
    if (!preg_match('/t\d\d/', $tournamentDir))
    {
        continue;
    }


    $fullTournamentDir = $dataDir . "/" . $tournamentDir;
    if (!is_dir($fullTournamentDir))
    {
        continue;
    }
    $roundDirs = array_diff(scandir($fullTournamentDir), array('.', '..'));
    foreach ($roundDirs as $roundDir) {
        if ($roundDir == 'a')
        {
            continue;
        }

        $fullRoundDir = $fullTournamentDir . "/" . $roundDir;
        $handFiles = array_diff(scandir($fullRoundDir), array('.', '..'));
        foreach ($handFiles as $handFile)
        {
            $fullHandFile = $fullRoundDir . "/" . $handFile;
            print $fullHandFile . "\n";

            $parser = new Parser();
            $parser->parseHand($fullHandFile);
        }

    }




}
