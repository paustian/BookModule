<?php

declare(strict_types=1);

namespace Paustian\BookModule\Entity;

use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * BookUserData entity class
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity(repositoryClass="Paustian\BookModule\Entity\Repository\BookUserDataRepository")
 * @ORM\Table(name="book_user_data")
 */
class BookUserDataEntity extends EntityAccess
{
    /**
     * cid field (record cid)
     *
     * @ORM\Id
     * @ORM\Column(type="integer", length=20)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $udid;
    
    /**
     * The number of the chapter
     * 
     * @ORM\Column(type="integer", length=20)
     */
    private $uid;
    
    /**
     * @ORM\Column(type="integer", length=20)
     */
    private $aid;
   
    /**
     * @ORM\Column(type="integer", length=20)
     */
    private $start;
    
    /**
     * @ORM\Column(type="integer", length=20)
     */
    private $end;
    
    
   
    /**
     * Constructor 
     */
    public function __construct()
    {
        $this->start = 0;
        $this->end = 0;
    }

    /**
     * @return int
     */
    public function getUdid() : int
    {
        return $this->udid;
    }

    /**
     * @param int $udid
     */
    public function setUdid(int $udid)
    {
        $this->udid = $udid;
    }

    /**
     * @return int
     */
    public function getUid() : int
    {
        return $this->uid;
    }

    /**
     * @param int $uid
     */
    public function setUid(int $uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return int
     */
    public function getAid() : int
    {
        return $this->aid;
    }

    /**
     * @param int $aid
     */
    public function setAid(int $aid)
    {
        $this->aid = $aid;
    }

    /**
     * @return int
     */
    public function getStart() : int
    {
        return $this->start;
    }

    /**
     * @param int $start
     */
    public function setStart(int $start)
    {
        $this->start = $start;
    }

    /**
     * @return int
     */
    public function getEnd() : int
    {
        return $this->end;
    }

    /**
     * @param int $end
     */
    public function setEnd(int $end)
    {
        $this->end = $end;
    }
}