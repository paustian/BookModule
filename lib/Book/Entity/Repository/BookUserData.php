<?php

/**
 * Copyright Timothy Paustian 2013 
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

// see http://www.doctrine-project.org/docs/orm/2.1/en/reference/working-with-objects.html#custom-repositories
// see http://www.doctrine-project.org/docs/orm/2.1/en/reference/query-builder.html

class Book_Entity_Repository_BookUserData extends Doctrine\ORM\EntityRepository
{
     public function getUserData($orderBy='', $where='')
    {
        $dql = "SELECT a FROM Book_Entity_BookUserData a";
        
        if (!empty($where)) {
            $dql .= ' WHERE ' . $where;
        }
        
        if(!empty($orderBy)){
            $dql .= " ORDER BY a.$orderBy";
        } 
        // generate query
        $query = $this->_em->createQuery($dql);


        try {
            $result = $query->getResult();
        } catch (Exception $e) {
            echo "<pre>getUserData failed\n";
            var_dump($e->getMessage());
            var_dump($query->getDQL());
            var_dump($query->getParameters());
            var_dump($query->getSQL());
            die;
        }
        return $result;
    }
    public function removeHighlightsForArticle($aid){
        $dql = "DELETE FROM Book_Entity_BookUserData a WHERE a.aid = " . $aid;
        // generate query
        $query = $this->_em->createQuery($dql);

        try {
            $result = $query->execute();
            $this->_em->flush();
        } catch (Exception $e) {
            echo "<pre>removeHighlightsForArticle Failed\n";
            var_dump($e->getMessage());
            var_dump($query->getDQL());
            var_dump($query->getParameters());
            var_dump($query->getSQL());
            die;
        }
        return $result;
    }
    
    public function getHighlights($uid, $aid){
       
        if (isset($aid)) {
            $where = "WHERE a.uid = '" . DataUtil::formatForStore($uid) . "'
				AND  a.aid = '" . DataUtil::formatForStore($aid) . "'";
        } else {
            $where = "WHERE a.uid = '" . DataUtil::formatForStore($uid) . "'";
        }
        
        $dql = "SELECT a FROM Book_Entity_BookUserData a " . $where . " ORDER BY a.start ASC";
        
         // generate query
        $query = $this->_em->createQuery($dql);


        try {
            $result = $query->getResult();
        } catch (Exception $e) {
            echo "<pre>getUserData failed\n";
            var_dump($e->getMessage());
            var_dump($query->getDQL());
            var_dump($query->getParameters());
            var_dump($query->getSQL());
            die;
        }
        return $result;
    }
}
?>
