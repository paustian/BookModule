<?php

declare(strict_types=1);
namespace Paustian\BookModule\Entity;

use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
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

    /**
     * @return int
     */
    public function getGid() : int {
        return $this->gid;
    }

    /**
     * @param int $gid
     */
    public function setGid(int $gid) {
        $this->gid = $gid;
    }

    /**
     * @return string
     */
    public function getTerm() : string {
        return $this->term;
    }

    /**
     * @param string $term
     */
    public function setTerm(string $term) {
        $this->term = $term;
    }

    /**
     * @return string
     */
    public function getDefinition() :string {
        return $this->definition;
    }

    /**
     * @param string $definition
     */
    public function setDefinition(string $definition) {
        $this->definition = \Paustian\BookModule\Helper\TagHelper::stripFrontAndBackPTags($definition);
    }

    /**
     * @return string
     */
    public function getUser() : string {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser(string $user) {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getUrl() : string {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url) {
        $this->url = $url;
    }
}