<?php

declare(strict_types=1);

namespace Paustian\BookModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Paustian\BookModule\Entity\BookFiguresEntity;

class BookFiguresRepository extends EntityRepository {

    /**
     * Get the figures for a book.
     *
     * @param int $bid
     * @return int|mixed|string
     */
    public function getFigures(int $bid = -1) : string {
        $qb = $this->_em->createQueryBuilder();

        // add select and from params
        $fields = array('u.fid', 'u.chap_number', 'u.fig_number', 'u.bid', 'u.title');
        $qb->select('u')
                ->from('PaustianBookModule:BookFiguresEntity', 'u');

        if ($bid > - 1) {
            $qb->where('(u.bid = ?1)')
                    ->setParameters([1 => $bid]);
        }
        $qb->addOrderBy('u.chap_number', 'ASC');
        $qb->addOrderBy('u.fig_number', 'ASC');

        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $figures = $query->getResult();
        return $figures;
    }

    /**
     * Grab the information for a figure.
     * @param int $fig_number
     * @param int $chap_number
     * @param int $book_number
     * @return array
     */
    public function findFigure(int $fig_number, int $chap_number, int $book_number) : array {
        $qb = $this->_em->createQueryBuilder();

        // add select and from params
        $fields = array('u.fig_number', 'u.chap_number', 'u.fig_number', 'u.bid');
        
        $qb->select('u')
                ->from('PaustianBookModule:BookFiguresEntity', 'u');

        $qb->where('(u.fig_number = ?1 AND u.chap_number = ?2 AND u.bid = ?3)')
                    ->setParameters([1 => $fig_number, 2 => $chap_number, 3 => $book_number]);

        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        return $query->getResult();
    }
}
