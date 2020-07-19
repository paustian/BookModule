<?php

declare(strict_types=1);
namespace Paustian\BookModule\Entity;

use Paustian\BookModule\Helper\TagHelper;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * BookFigures entity class
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity(repositoryClass="Paustian\BookModule\Entity\Repository\BookFiguresRepository")
 * @ORM\Table(name="book_figs")
 */
class BookFiguresEntity extends EntityAccess {

    /**
     * sid field (record sid)
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $fid;
    
    /**
     * @ORM\Column(type="integer", length=20)
     * @Assert\NotBlank()
     */
    private $fig_number;
    
    /**
     * @ORM\Column(type="integer", length=20)
     * @Assert\NotBlank()
     */
    private $chap_number;
    
    /**
     * @ORM\Column(type="integer", length=20)
     */
    private $bid;

    /**
     * Book img link
     * 
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $img_link = '';

    /**
     * Book Figure Title
     * 
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $title = '';

    
    /**
     * @ORM\Column(type="boolean")
     */
    private $perm;
    
    /**
     * Book contents
     * 
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $content;

    
    /**
     * Constructor 
     */
    public function __construct() {

        $this->title = '';
        $this->fig_number = 0;
        $this->chap_number = 0;
        $this->bid = 0;
        $this->contents = '';
        $this->title = '';
        $this->img_link= '';
    }

    /**
     * @return int
     */
    public function getFid() : int {
        return (int)$this->fid;
    }

    /**
     * @param int $fid
     */
    public function setFid(int $fid) {
        $this->fid = $fid;
    }

    /**
     * @return int
     */
    public function getFig_number() : int {
        return (int)$this->fig_number;
    }

    //This is a bit of a cludge to work with symfony
    //I did not want to rename the property.
    /**
     * @return int
     */
    public function getFigNumber() : int {
        return (int)$this->fig_number;
    }

    /**
     * @param int $fig_number
     */
    public function setFigNumber(int $fig_number){
        $this->fig_number = $fig_number;
    }

    /**
     * @param int $fig_number
     */
    public function setFig_number(int $fig_number) {
        $this->fig_number = $fig_number;
    }

    /**
     * @return int
     */
    public function getChapNumber() : int {
        return (int)$this->chap_number;
    }

    /**
     * @return int
     */
    public function getChap_number() : int {
        return (int)$this->chap_number;
    }

    /**
     * @param int $chap_number
     */
    public function setChapNumber(int $chap_number) {
        $this->chap_number = $chap_number;
    }

    /**
     * @param int $chap_number
     */
    public function setChap_number(int $chap_number) {
        $this->chap_number = $chap_number;
    }

    /**
     * @return int
     */
    public function getBid() : int {
        return (int)$this->bid;
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
    public function getImgLink() : string{
         return (string)$this->img_link;
    }

    /**
     * @return string
     */
    public function getImg_link() : string {
        return (string)$this->img_link;
    }

    /**
     * @param $img_link
     */
    public function setImgLink($img_link) {
        $this->img_link = $img_link;
    }

    /**
     * @param string $img_link
     */
    public function setImg_link(string $img_link) {
        $this->img_link = $img_link;
    }

    /**
     * @return string
     */
    public function getTitle() : string {
        return (string)$this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title) {
        $this->title = $title;
    }

    /**
     * @return bool
     */
    public function getPerm() : bool{
        return (bool)$this->perm;
    }

    /**
     * @param bool $perm
     */
    public function setPerm(bool $perm) {
        $this->perm = $perm;
    }

    /**
     * @return string
     */
    public function getContent() : string{
        return (string)$this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content) {
        $this->content = \Paustian\BookModule\Helper\TagHelper::stripFrontAndBackPTags($content);
    }

}