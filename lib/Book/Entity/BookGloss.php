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
 * @ORM\Entity(repositoryClass="Book_Entity_Repository_BookGloss")
 * @ORM\Table(name="book_gloss")
 */
class Book_Entity_BookGloss extends Zikula_EntityAccess {

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
     */
    private $term = '';

    /**
     * Glossary definition
     * @ORM\Column(type="text")
     */
    private $definition;

    /**
     * user that requested the definition
     * 
     * @ORM\Column(type="text")
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
        $this->definition = $definition;
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

?>
