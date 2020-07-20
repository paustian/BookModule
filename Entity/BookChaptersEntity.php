<?php

declare(strict_types=1);
namespace Paustian\BookModule\Entity;


use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
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

    /**
     * @return int
     */
    public function getCid() :int
    {
        return (int)$this->cid;
    }

    /**
     * @param int $cid
     */
    public function setCid(int $cid)
    {
        $this->cid = $cid;
    }

    /**
     * @return int
     */
    public function getNumber() : int
    {
        return (int)$this->number;
    }

    /**
     * @param int $number
     */
    public function setNumber(int $number)
    {
        $this->number = $number;
    }

    /**
     * @return int
     */
    public function getBid() :int
    {
        return (int)$this->bid;
    }

    /**
     * @param int $bid
     */
    public function setBid(int $bid)
    {
        $this->bid = $bid;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }
}

