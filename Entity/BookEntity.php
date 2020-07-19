<?php

declare(strict_types=1);
namespace Paustian\BookModule\Entity;

use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * Book entity class
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity(repositoryClass="Paustian\BookModule\Entity\Repository\BookRepository")
 * @ORM\Table(name="book")
 */
class BookEntity extends EntityAccess
{
    /**
     * bid field (record bid)
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
     * @Assert\NotBlank()
     * 
     */
    private $name = '';
    
    /**
     * Constructor 
     */
    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getBid() : int
    {
        return $this->bid;
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


