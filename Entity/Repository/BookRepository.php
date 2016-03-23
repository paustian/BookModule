<?php
namespace Paustian\BookModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Paustian\BookModule\Entity\BookEntity;

class BookRepository extends EntityRepository {

    public function getBooks($bid = 0) {
        //display a book interface.
        $qb = $this->_em->createQueryBuilder();

        // add select and from params
        $qb->select('u')
                ->from('PaustianBookModule:BookEntity', 'u');
        if ($bid > 0) {
            $qb->where('(u.bid = ?1)')
                    ->setParameters([1 => $bid]);
        }
        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $books = $query->getResult();
        return $books;
    }
    
    public function buildtoc($bid = 0, &$chapterids = "") {
        //get the list of books
        $booksEnts = $this->getBooks($bid);

        $books = array();
        $repoArt = $this->_em->getRepository('PaustianBookModule:BookArticlesEntity');
        $repoChap = $this->_em->getRepository('PaustianBookModule:BookChaptersEntity');
        foreach ($booksEnts as $bookEnt) {
            $book = array();
            //get list of chapters in the book, ordered by number (that is the the true is for)
            $chapterEnt = $repoChap->getChapters($bookEnt->getBid(), true);
            $chapters = array();
            $articles = array();
            foreach ($chapterEnt as $chapterEnt) {
                $chapter = array();
                //get all the articles in this chapter, ordered by number

                $articles = $repoArt->getArticles($chapterEnt->getCid(), true, false);
                $chapter['articles'] = $articles;
                $chapter['name'] = $chapterEnt->getName();
                $chapter['number'] = $chapterEnt->getNumber();
                $chapter['cid'] = $chapterEnt->getCid();
                $chapters[] = $chapter;
                $chapterids .= $chapter['cid'] . ",";
            }
            $book['chapters'] = $chapters;
            $book['bid'] = $bookEnt->getBid();
            $book['name'] = $bookEnt->getName();
            $books[] = $book;
        }
        //now grab all the articles that are undeclared
        $articleEnt = $repoArt->getArticles(0, false, false);
        $chapUndcl = array();
        $chapUndcl[0]['articles'] = $articleEnt;
        $chapUndcl[0]['name'] = 'Unassociated';
        $chapUndcl[0]['number'] = -1;
        $chapUndcl[0]['cid'] = 0;
        $bookUndl = array();
        $bookUndl['chapters'] = $chapUndcl;
        $bookUndl['bid'] = 0;
        $bookUndl['name'] = 'Unassociated';
        $books['undcl'] = $bookUndl;
        //tag the Unassociated "id" on the chapter ids
        $chapterids .= "0";
        return $books;
    }
}