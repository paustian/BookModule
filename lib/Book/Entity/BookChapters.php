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
 * @ORM\Entity(repositoryClass="Book_Entity_Repository_BookChapters")
 * @ORM\Table(name="book_chap")
 */
class Book_Entity_BookChapters extends Zikula_EntityAccess
{
    

    /**
     * cid field (record cid)
     *
     * @ORM\Id
     * @ORM\Column(type="integer", length=5)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $cid;
    
    /**
     * The number of the chapter
     * 
     * @ORM\Column(type="integer", length=5)
     */
    private $number;
    
    /**
     * @ORM\Column(type="integer", length=5)
     */
    private $bid;
    
    /**
     * Chapter Name
     * 
     * @ORM\Column(type="text")
     */
    private $name = '';
    
    /**
     * Constructor 
     */
    public function __construct()
    {
        $this->name = '';
        $this->number = 0;
    }
    
    public function getCid()
    {
        return $this->cid;
    }

    public function setCid($cid)
    {
        $this->cid = $cid;
    }
    
    public function getNumber()
    {
        return $this->number;
    }

    public function setNumber($number)
    {
        $this->number = $number;
    }
    
    public function getBook_id()
    {
        return $this->bid;
    }

    public function setBook_id($bid)
    {
        $this->bid = $bid;
    }
    
    public function getNname()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
}
?>
