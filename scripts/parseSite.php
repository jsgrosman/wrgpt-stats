<?php
/**
 * Created by PhpStorm.
 * User: jgrosman
 * Date: 1/2/19
 * Time: 10:15 AM
 */
require_once "../vendor/autoload.php";

use wrgpt\Parser;

$rounds = [
//    'b' => 1,
//    'c' => 152,
//    'd' => 261,
//    'e' => 404,
      'f' => 520
];

foreach ($rounds as $roundName => $roundStart)
{
    print "Round $roundName starting as $roundStart\n";
    $dir = "../data/t30/$roundName";
    if (!file_exists($dir))
    {
        mkdir($dir);
    }

    $tableNum = 1;
    while (true)
    {
        $handNum = $roundStart;
        $tableName = $roundName . $tableNum;

        print "Switching to $tableName\n";
        $foundOne = false;
        while (true) {
            $cacheFilename = $dir . "/${tableName}_${handNum}.txt";
            $serverFilename = "http://hands.wrgpt.org/${roundName}/hands/${tableName}_${handNum}.txt";

            print $cacheFilename . "\n";
            print $serverFilename . "\n";

            $serverFileExists = curl_file_exists($serverFilename);
            if (!file_exists($cacheFilename) && $serverFileExists) {
                print "Downloading... $serverFilename \n";
                file_put_contents($cacheFilename, file_get_contents($serverFilename));
            }
            else if (!file_exists($cacheFilename) && !$serverFileExists) {
                break;
            }

            print "Parsing $cacheFilename \n";

            $parser = new Parser();
            $parser->parseHand($cacheFilename);
            $handNum++;
            $foundOne = true;
        }

        if (!$foundOne)
        {
            break;
        }

        $tableNum++;
    }

}

function curl_file_exists($filename)
{
    $ch = curl_init($filename   );

    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// $retcode >= 400 -> not found, $retcode = 200, found.
    curl_close($ch);

    return $retcode !== 404;
}
