<?php
namespace Paustian\BookModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Paustian\BookModule\Entity\BookGlossEntity;

//set up for $orderBy array
//  $orderBy['col'] the column to order by
//  $orderBy['direction'] the direction
//  $orderBy = array('col' => 'u.term', 'direction' => 'ASC')

//set up for $where array
//  $where['cond'] the condition to test
//  $where['param'] the parameter
//  $where = array('cond' => 'u.term = ?1', 'paramkey' => 1, 'paramval => 'antibody'))
class BookGlossRepository extends EntityRepository {

    public function getGloss($letter = '', $orderBy = null, $where = null, $columns = 'u') {
        $qb = $this->_em->createQueryBuilder();
        $qb->select($columns)
                ->from('PaustianBookModule:BookGlossEntity', 'u');

        if ($orderBy != '') {
            $qb->orderBy($orderBy['col'], $orderBy['direction']);
        }

        if ($where != null) {
            $qb->where($where['cond']);
            $qb->setParameter($where['paramkey'], $where['paramval']);
        }

        if (($letter != '') && (strlen($letter) == 1)) {
            $qb->andWhere($qb->expr()->like('u.term', ':term'))->setParameter('term', $letter . '%');
        }
        $query = $qb->getQuery();

        // execute query
        $gloss = $query->getResult();
        return $gloss;
    }
    
    public function parseImportedGlossXML($xmlText){
        //An awesome function for parsing simple xml.
        $glossArray = simplexml_load_string($xmlText);
        $alreadydef = array();
        foreach($glossArray as $glossItem){
            //search for the term to see if it is there
            $currTerm = $this->getGloss('', null, 
                    ['cond' => 'u.term = ?1', 
                     'paramkey' => 1, 
                    'paramval' => $glossItem->term], 'u.term');
            if($currTerm){
                $alreadydef[] = $currTerm[0]['term'];
                continue;
            }
            $gloss = new BookGlossEntity();
            $gloss->setTerm($glossItem->term);
            $gloss->setDefinition($glossItem->definition);
            $this->_em->persist($gloss);
        }
        $this->_em->flush();
        return $alreadydef;
    }

}
