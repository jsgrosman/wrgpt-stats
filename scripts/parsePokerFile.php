<?php
/**
 * Created by PhpStorm.
 * User: jgrosman
 * Date: 1/2/19
 * Time: 10:15 AM
 */
require_once "../vendor/autoload.php";

use wrgpt\Parser;
use wrgpt\TableData;

foreach (TableData::$Tournament28['roundC'] as $tableName => $handLimit) {
    $round = substr($tableName,0, 1);
    $tableNum = substr($tableName,1);

    $dir = "../data/t28/$round";
    if (!file_exists($dir))
    {
        mkdir($dir);
    }

    for ($handNum = $handLimit[0]; $handNum <= $handLimit[1]; $handNum++) {

        $cacheFilename = $dir . "/${tableName}_${handNum}.txt";
        $serverFilename = "http://hands.wrgpt.org/${round}/hands/${tableName}_${handNum}.txt";

        if (file_exists($cacheFilename))
        {
            $url = $cacheFilename;
        }
        else
        {
            $url = $serverFilename;
            file_put_contents($cacheFilename, file_get_contents($serverFilename));
        }
        print "Parsing $url \n";

        $parser = new Parser();
        $parser->parseHand($url);
    }
}

//foreach (TableData::$Tournament28['roundC'] as $tableName => $handLimit) {
//    $round = substr($tableName,0, 1);
//    $tableNum = substr($tableName,1);
//
//    for ($handNum = $handLimit[0]; $handNum <= $handLimit[1]; $handNum++) {
//        $url = "http://hands.wrgpt.org/${round}/hands/${tableName}_${handNum}.txt";
//        $parser = new Parser();
//        $parser->parseHand($url);
//    }
//}
