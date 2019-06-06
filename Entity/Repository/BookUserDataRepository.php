<?php
namespace Paustian\BookModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Paustian\BookModule\Entity\BookUserDataEntity;

class BookUserDataRepository extends EntityRepository {

    public function getHighlights($aid, $uid) {
        //display a book interface.
        $qb = $this->_em->createQueryBuilder();
        
        // add select and from params
        $qb->select('u')
                ->from('PaustianBookModule:BookUserDataEntity', 'u');
        $qb->where('(u.uid = ?1 AND u.aid = ?2)')
                    ->setParameters([1 => $uid, 2 => $aid]);
        $qb->orderBy('u.start');
        
        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $userData = $query->getResult();
        return $userData;
    }
    
    public function checkHighlights($aid, $uid, $start, $end){
        $highlights = $this->getHighlights($aid, $uid);
        $highlightFound = false;
        foreach($highlights as $hItem){
            $hStart = $hItem->getStart();
            $hEnd = $hItem->getEnd();
            //check to see if this overlaps with any other selections
            //if it does delete it.
            if( (($start <= $hStart) && ($end >= $hEnd)) ||
                (($start >= $hStart) && ($start <= $hEnd)) ||
                (($end >= $hStart) && ($end <= $hEnd)) ){
                //We have a hit, This means to delete this selection
                //we can break the loop after hitting this.
                $this->_em->remove($hItem);
                $this->_em->flush();
                $highlightFound = true;
                break;
            }
        }
        return $highlightFound;
    }
    
    public function recordHighlight($aid, $uid, $start, $end){
        $highlight = new BookUserDataEntity();
        $highlight->setAid($aid);
        $highlight->setUid($uid);
        $highlight->setStart($start);
        $highlight->setEnd($end);
        $this->_em->persist($highlight);
        $this->_em->flush();
    }
}
