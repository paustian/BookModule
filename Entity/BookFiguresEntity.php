<?php
namespace Paustian\BookModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
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

    public function getFid() {
        return $this->fid;
    }

    public function setFid($fid) {
        $this->fid = $fid;
    }


    public function getFig_number() {
        return $this->fig_number;
    }
    
    //This is a bit of a cludge to work with symfony
    //I did not want to rename the property.
    public function getFigNumber(){
        return $this->fig_number;
    }
    
    public function setFigNumber($fig_number){
        $this->fig_number = $fig_number;
    }
    public function setFig_number($fig_number) {
        $this->fig_number = $fig_number;
    }
    
    public function getChapNumber(){
        return $this->chap_number;
    }
    public function getChap_number() {
        return $this->chap_number;
    }
    
    public function setChapNumber($chap_number) {
        $this->chap_number = $chap_number;
    }
    public function setChap_number($chap_number) {
        $this->chap_number = $chap_number;
    }

    public function getBid() {
        return $this->bid;
    }

    public function setBid($bid) {
        $this->bid = $bid;
    }
    
    public function getImgLink(){
         return $this->img_link;
    }
    public function getImg_link() {
        return $this->img_link;
    }
    
    public function setImgLink($img_link) {
        $this->img_link = $img_link;
    }
    
    public function setImg_link($img_link) {
        $this->img_link = $img_link;
    }
    
    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }
    
    public function getPerm() {
        return $this->perm;
    }

    public function setPerm($perm) {
        $this->perm = $perm;
    }
    
    public function getContent() {
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
    }

}

?>
