<?php
/**
 * Created by PhpStorm.
 * User: jgrosman
 * Date: 2019-02-28
 * Time: 22:31
 */

namespace wrgpt\parser;


class HandParser
{

    public function convertToArray($handText)
    {
        $lineByLine = explode("\n",$handText);
        return $lineByLine;
    }

}
