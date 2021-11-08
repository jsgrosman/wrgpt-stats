<?php
/**
 * Created by PhpStorm.
 * User: jgrosman
 * Date: 2019-02-28
 * Time: 22:30
 */

namespace wrgpt\parser;


class LineParser
{
    const START_LINE = '! History of this hand:';
    const END_LINE = '! Hand over';


    /**
     * @param ParserState $parserState
     * @param string $line
     * @return bool
     */
    public function checkStartOfHandHistory($parserState, $line)
    {
        $isStart =  strpos($line, self::START_LINE) === 0;
        if ($isStart)
        {
            $parserState->inHandHistory = true;
        }

        return $parserState;
    }

    /**
     * @param ParserState $parserState
     * @param string $line
     * @return bool
     */
    public function checkEndOfHandHistory($parserState, $line)
    {
        $isEnd =  strpos($line, self::END_LINE) === 0;
        if ($isEnd)
        {
            $parserState->inHandHistory = false;
        }

        return $parserState;
    }


}
