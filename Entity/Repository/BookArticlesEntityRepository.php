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

class BookArticlesEntityRepository extends Doctrine\ORM\EntityRepository {

    public function getArticles($orderBy = '', $where = '') {
        $dql = "SELECT a FROM Book_Entity_BookArticles a";

        if (!empty($where)) {
            $dql .= ' WHERE ' . $where;
        }

        if (!empty($orderBy)) {
            $dql .= " ORDER BY a.$orderBy";
        }
        // generate query
        $query = $this->_em->createQuery($dql);


        try {
            $result = $query->getResult();
        } catch (Exception $e) {
            echo "<pre>";
            var_dump($e->getMessage());
            var_dump($query->getDQL());
            var_dump($query->getParameters());
            var_dump($query->getSQL());
            die;
        }
        return $result;
    }

    public function updateBookIdForChapter($chapID, $newBookID) {
        $dql = "UPDATE Book_Entity_BookArticles a set a.bid = " . $newBookID . " where a.cid = " . $chapID; 
        $query = $this->_em->createQuery($dql);
        
        try {
            $result = $query->execute();
            $this->_em->flush();    
        } catch (Exception $e) {
            echo "<pre>Updating the chapter failed.\n";
            var_dump($e->getMessage());
            var_dump($query->getDQL());
            var_dump($query->getParameters());
            var_dump($query->getSQL());
            die;
        }
        return $result;
    }
    
    public function setCounter($aid, $counter){
        $dql = "UPDATE Book_Entity_BookArticles a set a.counter = ". $counter .  " WHERE a.aid = " . $aid;
        $query = $this->_em->createQuery($dql);
        $result = false;
        try {
            $query->execute();
            $this->_em->flush();
            $result = true;
        } catch (Exception $e) {
            echo "<pre>incrementCounter failed.\n";
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
