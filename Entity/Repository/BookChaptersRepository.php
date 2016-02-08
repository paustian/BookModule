<?php

namespace Paustian\BookModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Paustian\BookModule\Entity\BookChaptersEntity;

class BookChaptersRepository extends EntityRepository {

    public function getChapters($bid = -1, $order = false) {
        $qb = $this->_em->createQueryBuilder();

        // add select and from params
        $qb->select('u')
                ->from('PaustianBookModule:BookChaptersEntity', 'u');

        if ($bid > - 1) {
            $qb->where('(u.bid = ?1)')
                    ->setParameters([1 => $bid]);
        }
        if ($order) {
            $qb->orderBy('u.number', 'ASC');
        }
        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $chapters = $query->getResult();
        return $chapters;
    }

}
