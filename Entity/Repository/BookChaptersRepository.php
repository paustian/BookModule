<?php

declare(strict_types=1);

namespace Paustian\BookModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Paustian\BookModule\Entity\BookChaptersEntity;

class BookChaptersRepository extends EntityRepository {

    /**
     * Grab a list of the chapters in a book
     * @param int $bid
     * @param bool $order
     * @return array
     */

    public function getChapters(int $bid = -1, bool $order = false) : array {
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
