<?php
namespace Paustian\BookModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BookGloss entity class
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity(repositoryClass="Paustian\BookModule\Entity\Repository\BookGlossRepository")
 * @ORM\Table(name="book_gloss")
 */
class BookGlossEntity extends EntityAccess {

    /**
     * sid field (record sid)
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $gid;

    /**
     * Glossary Term
     * 
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $term = '';

    /**
     * Glossary definition
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $definition;

    /**
     * user that requested the definition
     * 
     * @ORM\Column(type="text")
     * 
     */
    private $user;

    /**
     * The url
     * 
     * @ORM\Column(type="text")
     */
    private $url;

    /**
     * Constructor 
     */
    public function __construct() {

        $this->term = '';
        $this->definition = '';
        $this->user = '';
        $this->url = '';
    }

    public function getGid() {
        return $this->gid;
    }

    public function setGid($gid) {
        $this->gid = $gid;
    }

    public function getTerm() {
        return $this->term;
    }

    public function setTerm($term) {
        $this->term = $term;
    }
    
    public function getDefinition() {
        return $this->definition;
    }

    public function setDefinition($definition) {
        $this->definition = \Paustian\BookModule\Helper\TagHelper::stripFrontAndBackPTags($definition);
    }
    
    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
    }
    
    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

}


