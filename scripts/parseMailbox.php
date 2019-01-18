<?php


$mailboxFile = "../data/Takeout/Mail/wrgpt_showdown.mbox";

$mailbox = file_get_contents($mailboxFile);

$lineByLine = explode("\n",$mailbox);

const DATE_FIELD = "Date: ";
const END_SIG = "Your automated Holdem dealer";

$dateForCurrentHand = null;
$tableForCurrentHand = null;
$handForCurrentHand = null;

$count = 0;

$currentHandText = null;
foreach ($lineByLine as $line)
{

    if (strpos($line, DATE_FIELD) > -1)
    {
        $dateString = substr($line, strlen(DATE_FIELD));
        print $dateString . "\n";
        $dateForCurrentHand = strtotime($dateString);
        $currentHandText = null;
    }
    else if  (strpos($line, END_SIG) > -1)
    {
        $tournamentNum = getTournamentNumber($dateForCurrentHand);
        $round = substr($tableForCurrentHand,0, 1);

        print $dateForCurrentHand . ":" . $tableForCurrentHand . ":" . $handForCurrentHand . "\n";
        print  "Tournament: " . getTournamentNumber($dateForCurrentHand) . "\n";
        $count++;

        $dir = "../data/t${tournamentNum}/${round}";
        print "trying to make $dir\n";
        if (!file_exists("../data/t${tournamentNum}"))
        {
            mkdir("../data/t${tournamentNum}");
        }
        if (!file_exists($dir))
        {
            mkdir($dir);
        }

        $cacheFilename = $dir . "/${tableForCurrentHand}_${handForCurrentHand}.txt";
        file_put_contents($cacheFilename, $currentHandText);

        // print $currentHandText . "\n";

//        if ($count++ > 20)
//        {
//            return;
//        };
    }
    else
    {
        $line = str_replace("Jason Grosman", "jasong", $line);
        $line = str_replace("\r", '', $line);
        $currentHandText .= $line . "\n";

        if (strpos($line, '! Table') === 0 &&
            strpos($line, 'reshuffle') === false)
        {
            $tableTokens = explode(',', $line);
            $tableForCurrentHand = explode(' ', $tableTokens[0])[2];
            $handForCurrentHand = explode(' ', $tableTokens[1])[2];
        }
    }

}

print "parsed $count \n";

function getTournamentNumber($handDate)
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
