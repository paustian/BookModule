<?php

declare(strict_types=1);

namespace Paustian\BookModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Paustian\BookModule\Entity\BookArticlesEntity;
use Paustian\BookModule\Entity\BookFiguresEntity;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;

class BookArticlesRepository extends ServiceEntityRepository
{
    private $controller;

    private $maxpixels = 700;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookArticlesEntity::class);
    }

    public function parseImportedChapterXML(string $xmlText) : void
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

    public function searchAndReplaceText(string $searchText, string $replaceText, bool $doPreview, int $cid, int &$count = 0) : array
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

    public function getArticles(int $cid = -1, bool $order = false, bool $content = false) : ?array
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

    public function incrementCounter(BookArticlesEntity $article)
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
    public function addfigures(string $ioText, AbstractController $contr)
    {
        $this->controller = $contr;
        //substitute all the figures
        //this is a legacy pattern
        $pattern = "|<!--\(Figure ([0-9]{1,2})-([0-9]{1,3})-([0-9]{1,3})(.*)\)-->|";
        $ioText = preg_replace_callback($pattern, array(&$this, "_inlinefigures"), $ioText);

        $pattern = "|{Figure ([0-9]{1,2})-([0-9]{1,3})-([0-9]{1,3})(.*)}|";
        $ioText = preg_replace_callback($pattern, array(&$this, "_inlinefigures"), $ioText);
        return $ioText;
    }

    /**
     * @param $words - the words to search for
     * @param $searchType - is this an AND or an OR search
     * @return array = the search results
     */
    public function getSearchResults(array $words, string $searchType) : array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
            ->from('PaustianBookModule:BookArticlesEntity', 'a');
        $count = count($words);
        switch($searchType){
            case 'AND':
                for($i=0; $i<$count; $i++){
                    $qb->andWhere('a.contents LIKE :word' . $i);
                    $qb->setParameter('word'. $i, '%' . $words[$i] . '%');
                }
                break;
            case 'OR':
                for($i=0; $i<$count; $i++){
                    $qb->orWhere('a.contents LIKE :word' . $i);
                    $qb->setParameter('word'. $i, '%' . $words[$i] . '%');
                }
                break;
            case 'EXACT':
                $phrase = implode(" ", $words);
                $qb->where('a.contents LIKE :word');
                $qb->setParameter('word', '%' . $phrase . '%');
                break;
        }
        $query = $qb->getQuery();
        $results = $query->getResult();
        return $results;
    }

    private function _inlinefigures(array $matches)
    {
        $book_number = (int)$matches[1];
        $chap_number = (int)$matches[2];
        $fig_number = (int)$matches[3];
        $movName = "canvas";
        $weight = 1000;
        $width = 0;
        $height = 0;
        $delay = 0;
        //grab the width and height if present. The synthax to use here is
        //4-26-1,640,480,movie the second number is the width, the third is the height,
        //the fourth is the name of any html5 canvas.
        $pieces = explode(',', rtrim($matches[4], "}"));
        $countPieces = count($pieces);
        switch ($countPieces){
            case 2:
                $width = (int)$pieces[1];
                break;
            case 3:
                $width = (int)$pieces[1];
                $height = (int)$pieces[2];
                break;
            case 4:
                $width = (int)$pieces[1];
                $height = (int)$pieces[2];
                $movName = $pieces[3];
                break;
            case 5:
                $width = (int)$pieces[1];
                $height = (int)$pieces[2];
                $movName = $pieces[3];
                $weight = (int)$pieces[4];
                break;
            case 6:
                $width = (int)$pieces[1];
                $height = (int)$pieces[2];
                $movName = $pieces[3];
                $weight = (int)$pieces[4];
                $delay = (int)$pieces[5];
                break;
            default:
                break;
        }


        $repo = $this->_em->getRepository('PaustianBookModule:BookFiguresEntity');
        $result = $repo->findFigure($fig_number, $chap_number, $book_number);
        //This is a bit of defensive coding in case a user has put in two figure that reference the same item.
        if(is_array($result)){
            $figure = array_shift($result);
        } else {
            $figure = $result;
        }
        if (!empty($figure)) {
            $figureText = $this->_renderFigure($figure, $width, $height, false, $movName, null, $weight, $delay);
        } else {
            $figureText = "";
        }
        return $figureText;
    }

    public function _renderFigure(BookFiguresEntity $figure,
                                  int $width = 0,
                                  int $height = 0,
                                  bool $stand_alone = false,
                                  string $movName = 'canvas',
                                  \Paustian\BookModule\Controller\UserController $inController = null,
                                  int $weight = 1000,
                                  int $delay = 0)
    {
        if(null !== $inController){
            $this->controller = $inController;
        }
//check to see if we have permission to use the figure
        if (0 != $figure->getPerm()) {
            $visible_link = $this->_buildlink($figure->getImgLink(), $figure->getTitle(), $width, $height, $stand_alone, $movName, $weight, $delay);
        } else {
            $visible_link = trans("This figure cannot be displayed because permission has not been granted yet.");
        }

        if ($stand_alone) {
            return $this->controller->renderFigure('@PaustianBookModule\User\book_user_displayfigure.html.twig', ['figure' => $figure,
                'visible_link' => $visible_link])->getContent();
        }
        return $this->controller->renderFigure('@PaustianBookModule\User\book_user_displayfigure.html.twig', ['figure' => $figure,
            'visible_link' => $visible_link])->getContent();
    }

    /**
     * _buildlink
     *
     * @param string $link
     * @param string $title
     * @param int $width
     * @param int $height
     * @param bool $stand_alone
     * @param string $movName
     * @param int $weight
     * @param int $delay
     * @return bool|string
     */
    private function _buildlink(string $link,
                                string $title = "",
                                int $width = 0,
                                int $height = 0,
                                bool $stand_alone = false,
                                string $movName = 'canvas',
                                int $weight = 1000,
                                int $delay = 0)
    {
        //if it is a image link, then set it up, else trust that the user
        //has set it up with the right tags.
        $alt_link = preg_replace("|<.*?>|", "", $title);
        $ret_link = "nothing";
        $pInfo = pathinfo($link);
        if(array_key_exists('extension', $pInfo)){
            $extension = $pInfo['extension'];
        } else {
            $extension = '';
        }


        switch ($extension) {
            case "html":
                $file_link = fopen($link, "r");
                if($file_link !== false){
                    $ret_link = fread($file_link, filesize($link));
                    fclose($file_link);
                } else {
                    $ret_link = "file not found";
                }

                break;
            case "gif":
            case "jpg":
            case "png":
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
                    $ret_link = $this->controller->renderFigure('@PaustianBookModule/User/book_user_buildlink1.html.twig', ['link' => $link,
                        'width' => $width,
                        'height' => $height,
                        'alt_link' => $alt_link])->getContent();
                } else {
                    $ret_link = $this->controller->renderFigure('@PaustianBookModule/User/book_user_buildlink2.html.twig', ['link' => $link,
                        'alt_link' => $alt_link])->getContent();
                }
                break;
            case "mov":
                if (($width == 0) || ($height == 0)) {
                    $width = 320;
                    $height = 336;
                }
                $ret_link = $this->controller->renderFigure('@PaustianBookModule/User/book_user_buildlink3.html.twig', ['link' => $link,
                    'width' => $width,
                    'height' => $height])->getContent();
                break;
            case "swf":
                if (($width == 0) || ($height == 0)) {
                    $image_data = getimagesize($link);
                    $width = $image_data[0];
                    $height = $image_data[1];
                }
                $ret_link = $this->controller->renderFigure('@PaustianBookModule\User\book_user_buildlink4.html.twig', ['link' => $link,
                    'width' => $width,
                    'height' => $height])->getContent();
                break;
            case "canvas":
                $jLink = $pInfo['dirname'] . "/" . $pInfo['filename'] . ".js";
                $ret_link = $this->controller->renderFigure('@PaustianBookModule/User/book_user_buildlink5.html.twig', ['link' => $link,
                    'width' => $width,
                    'height' => $height,
                    'jlink' => $jLink,
                    'movName' => $movName,
                    'weight' => $weight])->getContent();
                break;
            case 'music':
                $imglink = $pInfo['dirname'] . "/" . $pInfo['filename'];
                $ret_link = $this->controller->renderFigure('@PaustianBookModule/User/book_user_buildlink6.html.twig', ['link' => $link,
                    'width' => $width,
                    'height' => $height,
                    'movName' => $movName,
                    'weight' => $weight,
                    'delay' => $delay,
                    'imgLink' => $imglink])->getContent();
                break;
            default:
                $ret_link = $link;
        }
        return $ret_link;
    }

}
