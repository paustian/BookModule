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

class BookGlossEntityRepository extends Doctrine\ORM\EntityRepository
{
     public function getGloss($orderBy='', $where='', $columns='')
    {
        $dql = "SELECT ";
        if($columns != ''){
            foreach($columns as $column){
                $dql .= "$column ";
            }
        } else {
            $dql .= "a ";
        }
         $dql .= "FROM Book_Entity_BookGloss a";
        
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
            echo "<pre>";
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
