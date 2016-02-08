<?php
namespace Paustian\BookModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
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
