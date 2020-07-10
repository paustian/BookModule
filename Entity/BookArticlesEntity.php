<?php

declare(strict_types=1);
namespace Paustian\BookModule\Entity;

/**
 * StrainID2
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 */
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Paustian\Helper\TagHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * BookArticles entity class
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity(repositoryClass="Paustian\BookModule\Entity\Repository\BookArticlesRepository")
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
     * BookArticlesEntity constructor.
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

    /**
     * @return int
     */
    public function getAid() : int {
        return $this->aid;
    }

    /**
     * @param int $aid
     */
    public function setAid(int $aid) {
        $this->aid = $aid;
    }

    /**
     * @return string
     */
    public function getTitle() :string {
        return $this->title;
    }

    /**
     * @param sting $title
     */
    public function setTitle(sting $title) {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getCid() : int {
        return $this->cid;
    }

    /**
     * @param int $cid
     */
    public function setCid(int $cid) {
        $this->cid = $cid;
    }

    /**
     * @return int
     */
    public function getBid() : int {
        return $this->bid;
    }

    /**
     * @param int $bid
     */
    public function setBid(int $bid) {
        $this->bid = $bid;
    }

    /**
     * @return string
     */
    public function getContents() :string {
        return $this->contents;
    }

    /**
     * @param string $contents
     */
    public function setContents(string $contents) {
        $this->contents = $contents;
    }

    /**
     * @return int
     */
    public function getCounter() : int {
        return $this->counter;
    }

    /**
     * @param int $counter
     */
    public function setCounter(int $counter) {
        $this->counter = $counter;
    }

    /**
     * @return string
     */
    public function getLang() : string {
        return $this->lang;
    }

    /**
     * @param string $lang
     */
    public function setLang(string $lang) {
        $this->lang = $lang;
    }

    /**
     * @return int
     */
    public function getNext() : int {
        return $this->next;
    }

    /**
     * @param int $next
     */
    public function setNext(int $next) {
        $this->next = $next;
    }

    /**
     * @return int
     */
    public function getPrev() : int {
        return $this->prev;
    }

    /**
     * @param int $prev
     */
    public function setPrev(int $prev) {
        $this->prev = $prev;
    }

    /**
     * @return int
     */
    public function getNumber() :int {
        return $this->number;
    }

    /**
     * @param int $number
     */
    public function setNumber(int $number) {
        $this->number = $number;
    }

}