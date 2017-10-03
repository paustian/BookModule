<?php

namespace Paustian\BookModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Paustian\BookModule\Entity\BookFiguresEntity;

class BookArticlesRepository extends EntityRepository
{

    private $controller;

    private $maxpixels = 700;

    public function parseImportedChapterXML($xmlText)
    {
        $match = null;
        $pattern = "|<chapid>(.*?)</chapid>|";
        preg_match($pattern, $xmlText, $match);
        $chapid = $match[1];
        $chapter = $this->_em->getRepository('PaustianBookModule:BookChaptersEntity')->find($chapid);
        $pattern = "|<chapname>(.*?)</chapname>|";
        preg_match($pattern, $xmlText, $match);
        $chapter->setName($match[1]);
        $pattern = "|<chapnumber>(.*?)</chapnumber>|";
        preg_match($pattern, $xmlText, $match);
        $chapter->setNumber($match[1]);
        $pattern = "|<bookid>(.*?)</bookid>|";
        preg_match($pattern, $xmlText, $match);
        $chapter->setBid($match[1]);
        //we have the chapter data so save it.
        $this->_em->persist($chapter);
        //Now walk through all the aritcles
        $pattern = "|<section>(.*?)</section>|s";
        preg_match_all($pattern, $xmlText, $match);
        $articleArray = $match[1];
        $artMatch = null;
        foreach ($articleArray as $article) {
            $pattern = "|<artartid>(.*?)</artartid>|";
            preg_match($pattern, $article, $artMatch);
            $articleEnt = $this->find($artMatch[1]);
            //if the article is not found, just continue
            //maybe I should throw a warning?
            if (!isset($articleEnt)) {
                continue;
            }
            $pattern = "|<arttitle>(.*?)</arttitle>|";
            preg_match($pattern, $article, $artMatch);
            $articleEnt->setTitle($artMatch[1]);

            $pattern = "|<artchapid>(.*?)</artchapid>|";
            preg_match($pattern, $article, $artMatch);
            $articleEnt->setCid($artMatch[1]);

            $pattern = "|<artbookid>(.*?)</artbookid>|";
            preg_match($pattern, $article, $artMatch);
            $articleEnt->setBid($artMatch[1]);

            $pattern = "|<artcounter>(.*?)</artcounter>|";
            preg_match($pattern, $article, $artMatch);
            $articleEnt->setCounter($artMatch[1]);

            $pattern = "|<artlang>(.*?)</artlang>|";
            preg_match($pattern, $article, $artMatch);
            $articleEnt->setLang($artMatch[1]);

            $pattern = "|<artnext>(.*?)</artnext>|";
            preg_match($pattern, $article, $artMatch);
            $articleEnt->setNext($artMatch[1]);

            $pattern = "|<artprev>(.*?)</artprev>|";
            preg_match($pattern, $article, $artMatch);
            $articleEnt->setPrev($artMatch[1]);

            $pattern = "|<artnumber>(.*?)</artnumber>|";
            preg_match($pattern, $article, $artMatch);
            $articleEnt->setNumber($artMatch[1]);

            $pattern = "|<contents>(.*?)</contents>|s";
            preg_match($pattern, $article, $artMatch);
            $articleEnt->setContents($artMatch[1]);

            $this->_em->persist($articleEnt);
        }
        $this->_em->flush();
    }

    public function searchAndReplaceText($searchText, $replaceText, $doPreview, $cid, &$count = 0)
    {
        $articles = $this->getArticles($cid, false, true);
        $resultArray = array();
        foreach ($articles as $article) {
            $contents = $article->getContents();
            $newContents = "";
            $spc_count = 0;
            if ($doPreview) {
                $newContents .= "<h3>" . $article->getTitle() . "</h3>\n";
                $newContents .= preg_replace($searchText, "<b style=\"color:blue; font-size:large;\">" . $replaceText . "</b>", $contents, -1, $spc_count);
                $resultArray[] = $newContents;
            } else {
                $newContents = preg_replace($searchText, $replaceText, $contents, -1, $spc_count);
                $article->setContents($newContents);
                $this->_em->merge($article);
            }
            $count += $spc_count;
        }
        if (!$doPreview) {
            $this->_em->flush();
        }
        return $resultArray;
    }

    public function getArticles($cid = -1, $order = false, $content = false)
    {
        $qb = $this->_em->createQueryBuilder();

        //if content is true, grab all the content
        if ($content) {
            $qb->select('u')
                ->from('PaustianBookModule:BookArticlesEntity', 'u');
        } else {
            $fields = array('u.title', 'u.aid', 'u.number');
            $qb->select($fields)
                ->from('PaustianBookModule:BookArticlesEntity', 'u');
        }

        if ($cid > -1) {
            $qb->where('(u.cid = ?1)')
                ->setParameters([1 => $cid]);
        }
        if ($order) {
            $qb->orderBy('u.number', 'ASC');
        }
        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $articles = $query->getResult();
        return $articles;
    }

    public function incrementCounter($article)
    {

        $count = $article->getCounter();
        $count++;
        $article->setCounter($count);
        $this->_em->persist($article);
        $this->_em->flush();
    }

    //book_user_addfigures
//I factored this out of the above so that I could call it from the admin
//code for exporting the chapters.
    public function addfigures($ioText, \Zikula\Core\Controller\AbstractController $contr)
    {
        $this->controller = $contr;
        //substitute all the figures
        //this is a legacy pattern
        $pattern = "|<!--\(Figure ([0-9]{1,2})-([0-9]{1,3})-([0-9]{1,3})\)-->|";
        $ioText = preg_replace_callback($pattern, array(&$this, "_inlinefigures"), $ioText);

        $pattern = "|{Figure ([0-9]{1,2})-([0-9]{1,3})-([0-9]{1,3}).*}|";
        $ioText = preg_replace_callback($pattern, array(&$this, "_inlinefigures"), $ioText);
        return $ioText;
    }

    /**
     * @param $words - the words to search for
     * @param $searchType - is this an AND or an OR search
     * @return array = the search results
     */
    public function getSearchResults($words, $searchType)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
            ->from('PaustianBookModule:BookArticlesEntity', 'a');
        $count = count($words);
        for($i = 0; $i < $count; $i++) {
            if ($searchType == 'AND') {
                $qb->andWhere('a.title LIKE :word' . $i);
                $qb->andWhere('a.contents LIKE :word' . $i);
            } else {
                $qb->orWhere('a.title LIKE :word'. $i);
                $qb->orWhere('a.contents LIKE :word' . $i);
            }
            $qb->setParameter('word'. $i, '%' . $words[$i] . '%');
        }
        $query = $qb->getQuery();
        $results = $query->getResult();
        return $results;
    }

    private function _inlinefigures($matches)
    {
        $book_number = $matches[1];
        $chap_number = $matches[2];
        $fig_number = $matches[3];

        //grab the width and heigh if present. The synthax to use here is
        //4-26-1,640,480 the second number is the width, the third is the height
        $pieces = explode(',', rtrim($matches[0], "}"));
        if (count($pieces) > 1) {
            $width = $pieces[1];
            $height = $pieces[2];
        }
        if (!isset($width)) {
            $width = 0;
        }
        if (!isset($height)) {
            $height = 0;
        }

        $repo = $this->_em->getRepository('PaustianBookModule:BookFiguresEntity');
        $figure = $repo->findFigure($fig_number, $chap_number, $book_number);

        if ($figure != null) {
            $figureText = $this->_renderFigure($figure, $width, $height, false);
        } else {
            $figureText = "";
        }
        return $figureText;
    }

    public function _renderFigure(BookFiguresEntity $figure, $width = 0, $height = 0, $stand_alone = false, $contr = null)
    {
        if ($contr != null) {
            $this->controller = $contr;
        }
//check to see if we have permission to use the figure
        if ($figure->getPerm() != 0) {
            $visible_link = $this->_buildlink($figure->getImgLink(), $figure->getTitle(), $width, $height, true, false, true, $stand_alone);
        } else {
            $visible_link = __("This figure cannot be displayed because permission has not been granted yet.");
        }

        if ($stand_alone) {
            return $this->controller->render('PaustianBookModule:User:book_user_displayfigure.html.twig', ['figure' => $figure,
                'visible_link' => $visible_link])->getContent();
        }
        return $this->controller->render('PaustianBookModule:User:book_user_displayfigure.html.twig', ['figure' => $figure,
            'visible_link' => $visible_link])->getContent();
    }

    /**
     * _buildlink
     *
     * @param $link
     * @param string $title
     * @param int $width
     * @param int $height
     * @param string $controller
     * @param string $loop
     * @param string $autoplay
     * @param bool $stand_alone
     * @return bool|string
     */
    private function _buildlink($link, $title = "", $width = 0, $height = 0, $controller = "true", $loop = "false", $autoplay = "true", $stand_alone = false)
    {
        //if it is a image link, then set it up, else trust that the user
        //has set it up with the right tags.
        $alt_link = preg_replace("|<.*?>|", "", $title);
        $ret_link = "nothing";

        if (strstr($link, ".html")) {
            $file_link = fopen($link, "r");
            $ret_link = fread($file_link, filesize($link));
            fclose($file_link);
        } else
            if ((strstr($link, ".gif")) || (strstr($link, ".jpg")) || (strstr($link, ".png"))) {
                //This was added to prevent failures on file missing. For some reason getimagesize sometimes throws
                //an error, even though the path to the file is correct
                $docRoot = $_SERVER['DOCUMENT_ROOT'];
                $test_link = $docRoot . $link;
                if (file_exists($test_link)) {
                    $image_data = getimagesize($test_link);
                    if ($width == 0) {
                        $width = $image_data[0];
                    }
                    if ($height == 0) {
                        $height = $image_data[1];
                    }
                    //if the image is too wide, then shrink it to be no larger than max pixels.
                    if (!$stand_alone && $width > $this->maxpixels) {
                        $height = round($height * $this->maxpixels / $width);
                        $width = $this->maxpixels;
                    }
                    $ret_link = $this->controller->render('PaustianBookModule:User:book_user_buildlink1.html.twig', ['link' => $link,
                        'width' => $width,
                        'height' => $height,
                        'alt_link' => $alt_link])->getContent();
                } else {
                    $ret_link = $this->controller->render('PaustianBookModule:User:book_user_buildlink2.html.twig', ['link' => $link,
                        'alt_link' => $alt_link])->getContent();
                }
            } else
                if (strstr($link, ".mov")) {
                    if (($width == 0) || ($height == 0)) {
                        $width = 320;
                        $height = 336;
                    }
                    $ret_link = $this->controller->render('PaustianBookModule:User:book_user_buildlink3.html.twig', ['link' => $link,
                        'width' => $width,
                        'height' => $height])->getContent();
                } else
                    if (strstr($link, ".swf")) {
                        if (($width == 0) || ($height == 0)) {
                            $image_data = getimagesize($link);
                            $width = $image_data[0];
                            $height = $image_data[1];
                        }
                        $ret_link = $this->controller->render('PaustianBookModule:User:book_user_buildlink4.html.twig', ['link' => $link,
                            'width' => $width,
                            'height' => $height])->getContent();
                    } else {
                        $ret_link = $link;
                    }
        return $ret_link;
    }

}
