<?php

namespace Paustian\BookModule\API;


use Paustian\BookModule\API\TextRankFacade;
use Paustian\BookModule\API\Tool\StopWords\English;
use Paustian\BookModule\API\Tool\Summarize;

class TextSummarizer
{
    private $textRank;
    private $level;

    public function __construct()
    {
        $stopWords = new English();
        $this->textRank = new TextRankFacade();
        $this->textRank->setStopWords($stopWords);
    }

    public function setSumLevel(int $inLevel, int $maxRank){
        $this->level = 5 - $inLevel + 1;
        $maxRank = 5 - $maxRank + 1;
        //We don't let summarization get below the set config level
        if($this->level < $maxRank){
            $this->level = $maxRank;
        }
        if($this->level > 5){
            $this->level = 5;
        }
    }

    public function getSumLevel(): int {
        return $this->level;
    }

    public function summarizeText($inText){
        if($this->level >= 5){
            return $inText;
        }
        return preg_replace_callback("|<p>(.*?)</p>|s", [&$this, "summarizeTextCallback"], $inText);
    }

    public function summarizeTextCallback($matches)
    {
        if (strlen($matches[1]) < 250) {
            return "<p>" . $matches[1] . "</p>";
        }
        $textToSummarize = $matches[1];
        $sentences = $this->textRank->summarizeTextFreely(
            $textToSummarize,
            10,
            $this->level,
            Summarize::GET_ALL_IMPORTANT);
        $retText = "<p>";
        foreach ($sentences as $sentence) {
            $retText .= $sentence . " ";
        }
        return $retText . "</p>";
    }
}