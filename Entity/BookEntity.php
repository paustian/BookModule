<?php

/**
 * StrainID2
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 */

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * Book entity class
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity(repositoryClass="Book_Entity_Repository_Book")
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
