<?php
namespace Paustian\BookModule\Entity;


use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * BookChapters entity class
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity(repositoryClass="Paustian\BookModule\Entity\Repository\BookChaptersRepository")
 * @ORM\Table(name="book_chap")
 */
class BookChaptersEntity extends EntityAccess
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
     * @Assert\NotBlank()
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
     * @Assert\NotBlank()
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

