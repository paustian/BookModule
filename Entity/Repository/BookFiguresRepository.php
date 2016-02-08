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
}
