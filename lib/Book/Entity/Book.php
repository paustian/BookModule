<?php

/**
 * StrainID2
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 */
use Doctrine\ORM\Mapping as ORM;

/**
 * StrainID2 entity class
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity(repositoryClass="Book_Entity_Repository_Book")
 * @ORM\Table(name="book")
 */
class Book_Entity_Book extends Zikula_EntityAccess
{
    

    /**
     * sid field (record sid)
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $bid;

    /**
     * Book Name
     * 
     * @ORM\Column(type="text")
     */
    private $name = '';
    
    /**
     * Constructor 
     */
    public function __construct()
    {
    }

    public function getBid()
    {
        return $this->bid;
    }

    public function setBid($bid)
    {
        $this->bid = $bid;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
}

?>
