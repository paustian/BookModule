<?php
namespace Paustian\BookModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Paustian\BookModule\Entity\BookArticlesEntity;


class BookArticlesRepository extends EntityRepository {

    public function getArticles($cid = -1, $order = false, $content = false) {
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

        if ($cid > - 1) {
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
    
    public function parseImportedChapterXML($xmlText){
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
        foreach($articleArray as $article){
            $pattern = "|<artartid>(.*?)</artartid>|";
            preg_match($pattern, $article, $artMatch);
            $articleEnt = $this->find($artMatch[1]);
            //if the article is not found, just continue
            //maybe I should throw a warning?
            if(!isset($articleEnt)){
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
    
    public function searchAndReplaceText($searchText, $replaceText, $doPreview, $cid){
        $articles = $this->getArticles($cid, false, true);
        $resultArray = array();
        foreach ($articles as $article){
            $contents = $article->getContents();
            $newContents = "";
            if($doPreview){
                $newContents .= "<h3>" . $article->getTitle() . "</h3>\n";
                $newContents .= preg_replace($searchText, "<b>" . $replaceText . "</b>", $contents);
                $resultArray[] = $newContents;
            } else {
                $newContents = preg_replace($searchText, $replaceText, $contents);
                $article->setContents($newContents);
                $this->_em->merge($article);
            }
        }
        if(!$doPreview){
            $this->_em->flush();
        }
        return $resultArray;
    }
 

}
