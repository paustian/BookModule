<?php

namespace Paustian\BookModule\API;


use Paustian\BookModule\API\TextRankFacade;
use Paustian\BookModule\API\Tool\StopWords\English;
use Paustian\BookModule\API\Tool\Summarize;

class TextSummarizer
{
    private $textRank;

    function __construct()
    {
        $stopWords = new English();
        $this->textRank = new TextRankFacade();
        $this->textRank->setStopWords($stopWords);
    }

    function summarizeText($inText){
        return preg_replace_callback("|<p>(.*?)</p>|", [&$this, "summarizeTextCallback"], $inText);
    }

    function summarizeTextCallback($matches)
    {
        if (strlen($matches[1]) < 250) {
            return $matches[1];
        }
        $textToSummarize = $matches[1];
        $sentences = $this->textRank->summarizeTextFreely(
            $textToSummarize,
            10,
            1,
            Summarize::GET_ALL_IMPORTANT);
        $retText = "<p>";
        foreach ($sentences as $sentence) {
            $retText .= $sentence . " ";
        }
        return $retText . "</p>";
    }
}