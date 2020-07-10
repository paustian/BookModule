<?php

declare(strict_types=1);
namespace Paustian\BookModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Paustian\BookModule\Entity\BookGlossEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;

//set up for $orderBy array
//  $orderBy['col'] the column to order by
//  $orderBy['direction'] the direction
//  $orderBy = array('col' => 'u.term', 'direction' => 'ASC')

//set up for $where array
//  $where['cond'] the condition to test
//  $where['param'] the parameter
//  $where = array('cond' => 'u.term = ?1', 'paramkey' => 1, 'paramval => 'antibody'))
class BookGlossRepository extends EntityRepository {

    /**
     * @param string $letter
     * @param array|null $orderBy
     * @param array|null $where
     * @param string $columns
     * @return int|mixed|string
     */
    public function getGloss(string $letter = '', array $orderBy = null, array $where = null, array $columns = ['u']) :array {
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

    /**
     *
     * Given a term, see if it is defined
     * @param string $inTerm
     * @return string
     */
    public function getTerm(string $inTerm) : string {
        $glossItem = $this->findOneByTerm($inTerm);
        return $glossItem;
    }

    /**
     * Find terms that don't have definitions. These are proposed by users.
     * @return array
     */
    
    public function getUndefinedTerms() : array{
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u')
                ->from('PaustianBookModule:BookGlossEntity', 'u');
        $qb->where('u.definition = :def1');
        $qb->orWhere('u.definition = :def2');   
        $qb->setParameters(new ArrayCollection(array(
                  new Parameter('def1', ''),
                  new Parameter('def2', 'TBD'))));
        $query = $qb->getQuery();
        // execute query
        $gloss = $query->getResult();
        return $gloss;
        
    }

    /**
     * Given a list of termsin xml, parse it and return the array of terms for display
     *
     * @param string $xmlText
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
            
    public function parseImportedGlossXML(string $xmlText) :array {
        //An awesome function for parsing simple xml.
        $glossArray = simplexml_load_string($xmlText);
        $alreadydef = array();
        foreach($glossArray as $glossItem){
            //search for the term to see if it is there
            $currTerm = $this->getGloss('', null, 
                    ['cond' => 'u.term = ?1', 
                     'paramkey' => 1, 
                    'paramval' => $glossItem->term], ['u.term']);
            if($currTerm){
                $alreadydef[] = $currTerm[0]['term'];
                continue;
            }
            $gloss = new BookGlossEntity();
            $gloss->setTerm($glossItem->term());
            $gloss->setDefinition($glossItem->definition());
            $this->_em->persist($gloss);
        }
        $this->_em->flush();
        return $alreadydef;
    }

}
