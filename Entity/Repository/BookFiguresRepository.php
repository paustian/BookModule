<?php
namespace Paustian\BookModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Paustian\BookModule\Entity\BookFiguresEntity;

class BookFiguresRepository extends EntityRepository {

    public function getFigures($bid = -1) {
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
    
    public function findFigures($fig_number, $chap_number, $book_number){
        $qb = $this->_em->createQueryBuilder();

        // add select and from params
        $fields = array('u.fig_number', 'u.chap_number', 'u.fig_number', 'u.bid');
        
        $qb->select('u')
                ->from('PaustianBookModule:BookFiguresEntity', 'u');

        $qb->where('(u.fig_number = ?1 AND u.chap_number = ?2 AND u.bid = ?3)')
                    ->setParameters([1 => $fig_number, 2 => $chap_number, 3 => $bid]);

        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $figures = $query->getResult();
        return $figures;
    }
}
