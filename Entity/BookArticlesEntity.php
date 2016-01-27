<?php
namespace Paustian\BookModule\Entity;

/**
 * StrainID2
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 */
use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * BookArticles entity class
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="book_art")
 */
class BookArticlesEntity extends EntityAccess {

    /**
     * sid field (record sid)
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $aid;

    /**
     * Book Article Title
     * 
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $title = '';

    /**
     * @ORM\Column(type="integer", length=5)
     */
    private $cid;

    /**
     * @ORM\Column(type="integer", length=5)
     */
    private $bid;

    /**
     * Book contents
     * 
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * 
     */
    private $contents;

    /**
     * Book counter, how many accesses
     * 
     * @ORM\Column(type="integer", length=11)
     */
    private $counter;

    /**
     * Book language
     * 
     * @ORM\Column(type="text", length=30)
     */
    private $lang;

    /**
     * Book next, the next article to link to
     * 
     * @ORM\Column(type="integer", length=10)
     * @Assert\NotBlank()
     * 
     */
    private $next;

    /**
     * Book prev, the prev article to link to
     * 
     * @ORM\Column(type="integer", length=10)
     * @Assert\NotBlank()
     */
    private $prev;

    /**
     * Book article number The order in which it should be displayed
     * 
     * @ORM\Column(type="integer", length=10)
     * @Assert\NotBlank()
     */
    private $number;

    /**
     * Constructor 
     */
    public function __construct() {

        $this->title = '';
        $this->cid = 0;
        $this->bid = 0;
        $this->contents = '';
        $this->counter = 0;
        $this->lang = 'eng';
        $this->next = 0;
        $this->prev = 0;
        $this->aid = 0;
        $this->number = 0;
    }

    public function getAid() {
        return $this->aid;
    }

    public function setAid($aid) {
        $this->aid = $aid;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getCid() {
        return $this->cid;
    }

    public function setCid($cid) {
        $this->cid = $cid;
    }

    public function getBid() {
        return $this->bid;
    }

    public function setBid($bid) {
        $this->bid = $bid;
    }

    public function getContents() {
        return $this->contents;
    }

    public function setContents($contents) {
        $this->contents = $contents;
    }

    public function getCounter() {
        return $this->counter;
    }

    public function setCounter($counter) {
        $this->counter = $counter;
    }

    public function getLang() {
        return $this->lang;
    }

    public function setLang($lang) {
        $this->lang = $lang;
    }

    public function getNext() {
        return $this->next;
    }

    public function setNext($next) {
        $this->next = $next;
    }

    public function getPrev() {
        return $this->prev;
    }

    public function setPrev($prev) {
        $this->prev = $prev;
    }

    public function getNumber() {
        return $this->number;
    }

    public function setNumber($number) {
        $this->number = $number;
    }

}

?>
