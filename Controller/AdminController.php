<?php

// ----------------------------------------------------------------------
// Original Author of file: Timothy Paustian
// Purpose of file:  Book administration display functions
// ----------------------------------------------------------------------

namespace Paustian\BookModule\Controller;

use Zikula\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use SecurityUtil;
use ModUtil;
use Paustian\BookModule\Entity\BookEntity;
use Paustian\BookModule\Entity\BookArticlesEntity;
use Paustian\BookModule\Entity\BookChaptersEntity;
use Paustian\BookModule\Entity\BookFiguresEntity;
use Paustian\BookModule\Entity\BookGlossEntity;
use Paustian\BookModule\Entity\BookUserDataEntity;
use Paustian\BookModule\Form\Book;
use Paustian\BookModule\Form\Chapter;
use Paustian\BookModule\Form\Article;

class AdminController extends AbstractController {

    static public function url_replace_func($matches) {
        //you have to do two amp amp because the browser translates one of them.
        //first replace the amp
        $ret_text = 'href="' . preg_replace('|&([^a][^m][^p][^;])|', '&amp;amp;$1', $matches[1]) . '"';

        return $ret_text;
    }

    /**
     * @Route("")
     * @param request - the incoming request.
     * The main entry point
     * 
     * @return Response The rendered output consisting mainly of the admin menu
     * 
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function indexAction(Request $request) {
        //security check
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        // Return a page of menu items.
        return new Response($this->render('PaustianBookModule:Admin:book_admin_menu.html.twig'));
    }

    /**
     * 
     * @Route("/edit/{book}")
     * Create a new book. This presents the form for giving a title to the book
     * 
     * @return Response The rendered output of the modifyconfig template.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function editAction(Request $request, BookEntity $book = null) {
        $doMerge = false;
        if (null === $book) {
            if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADD)) {
                throw new AccessDeniedException($this->__("You do not have permission to edit books."));
            }
            $book = new BookEntity();
        } else {
            if (!SecurityUtil::checkPermission($this->name . '::', $book->getBid() . '::', ACCESS_ADD)) {
                throw new AccessDeniedException($this->__("You do not have permission to edit this book."));
            }
            $doMerge = true;
        }

        //I need to add the use declaration for this class. 
        $form = $this->createForm(new Book(), $book);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if ($doMerge) {
                $em->merge($book);
            } else {
                $em->persist($book);
            }
            $em->flush();

            $this->addFlash('status', 'Book Saved');
            return $this->redirect($this->generateUrl('paustianbookmodule_admin_edit'));
        }

        return $this->render('PaustianBookModule:Admin:book_admin_editbook.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/delete/{book}")
     * @param Request $request
     * @param BookEntity $book
     */
    public function deleteAction(Request $request, BookEntity $book = null) {
        if (!SecurityUtil::checkPermission('book::', $book->getBid() . "::", ACCESS_DELETE)) {
            throw new AccessDeniedException($this->__("You do not have permission to delete that book."));
        }
        $response = $this->redirect($this->generateUrl('paustianbookmodule_admin_modify'));
        if ($book == null) {
            //you want the edit interface, which has a delete option.
            return $response;
        }
        //I need to walk the chapters and remove any reference to this book
        //Set the chapters to 0
        $chapters = $this->_getChapters($book->getBid());
        foreach ($chapters as $chapter) {
            $chapter->setBid(0);
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($book);
        $em->flush();
        $this->addFlash('status', __('Book Deleted.'));
        return $response;
    }

    /**
     * 
     * @Route("/editchapter/{chapter}")
     * @param Request $request
     * @param \Paustian\BookModule\Controller\BookChaptersEntity $chapter
     * @return RedirectRespsonse or Response
     * @throws AccessDeniedException
     */
    public function editchapterAction(Request $request, BookChaptersEntity $chapter = null) {
        if (!SecurityUtil::checkPermission($this->name . '::', "::", ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        $doMerge = false;
        if (null === $chapter) {
            $chapter = new BookChaptersEntity();
        } else {
            $doMerge = true;
        }
        $em = $this->getDoctrine()->getManager();
        $items = $this->_getBooks();
        if ($items === null) {
            //There are no books
            $this->addFlash('status', __('There are no books. Create a book first.'));
            return $this->redirect($this->generateUrl('paustianbookmodule_admin_edit'));
        }

        $form = $this->createForm(new Chapter(), $chapter);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $bid = $request->get('book');
            $chapter->setBid($bid);
            if ($doMerge) {
                $em->merge($chapter);
            } else {
                $em->persist($chapter);
            }
            $em->flush();

            $this->addFlash('status', 'Chapter Saved');
            return $this->redirect($this->generateUrl('paustianbookmodule_admin_editchapter'));
        }

        return $this->render('PaustianBookModule:Admin:book_admin_editchapter.html.twig', array(
                    'form' => $form->createView(),
                    'books' => $items,
                    'chapter' => $chapter
        ));
    }

    /**
     * @Route("/editarticle/{article}")
     * 
     * edit an article.
     * @param Request $request
     * @param \Paustian\BookModule\Controller\BookArticlesEntity $article
     * @return RedirectResponse or Response
     */
    public function editarticleAction(Request $request, BookArticlesEntity $article = null) {
        if (!SecurityUtil::checkPermission($this->name . '::', "::", ACCESS_ADD)) {
            throw new AccessDeniedException();
        }
        $doMerge = false;
        if (null === $article) {
            $article = new BookArticlesEntity();
        } else {
            $doMerge = true;
        }

        $form = $this->createForm(new Article(), $article);

        $form->handleRequest($request);

        if ($form->isValid()) {
            //upon creation, articles are not attached to books
            //you attach them later in a drag and drop interface
            $em = $this->getDoctrine()->getManager();
            if ($doMerge) {
                $em->merge($article);
            } else {
                //This is a new article so Book and Chapter not set.
                $article->setBid(0);
                $article->setCid(0);
                $em->persist($article);
            }
            $em->flush();

            $this->addFlash('status', 'Article Saved');
            return $this->redirect($this->generateUrl('paustianbookmodule_admin_editarticle'));
        }

        return $this->render('PaustianBookModule:Admin:book_admin_editarticle.html.twig', [
                    'form' => $form->createView()]);
    }

    /**
     * @Route("/editfigure/{figure}")
     * 
     * @param Request $request
     * @param \Paustian\BookModule\Controller\BookFiguresEntity $figure
     * @return RedirectResponse or Repsonse;
     */
    public function editfigureAction(Request $request, BookFiguresEntity $figure = null) {
        return $this->redirect($this->generateUrl('paustianbookmodule_admin_edit'));
        /*        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADD)) {
          return LogUtil::registerPermissionError();
          }


          $books = ModUtil::apiFunc('Book', 'user', 'getall');
          if ($books == false) {
          return LogUtil::addWarningPopup($this->__('You have to create a book before you can create figures'));
          }

          $bookMenu = array();
          foreach ($books as $book_item) {
          if (SecurityUtil::checkPermission('Book::Chapter', "$book_item[bid]::.*", ACCESS_ADD)) {
          $bookMenu[$book_item['bid']] = $book_item['name'];
          }
          }
          $this->view->assign('books', $bookMenu);
          //There is no data to grab to insert in this form.
          //Figures in this design are basically free and can
          //be added irrespective of books, chapters or articles
          return $this->view->fetch('book_admin_newfigure.tpl'); */
    }

    /**
     * @Route("/editglossary/{gloss}")
     * 
     * @param Request $request
     * @param BookGlossEntity $gloss
     * @return type
     */
    public function editglossaryAction(Request $request, BookGlossEntity $gloss = null) {
        return $this->redirect($this->generateUrl('paustianbookmodule_admin_edit'));
        /*        // Security check
          //If you have access to the module, you can add glossary items.
          if (!SecurityUtil::checkPermission('Book::', '.*::', ACCESS_ADD)) {
          return LogUtil::registerPermissionError();
          }

          // Return the output that has been generated by this function
          return $this->view->fetch('book_admin_newglossary.tpl'); */
    }

    /**
     * @Route("/modify")
     * @param Request $request
     * @return type
     */
    public function modifyAction(Request $request) {
        $books = $this->_getBooks();

        return $this->render('PaustianBookModule:Admin:book_admin_modifybook.html.twig', ['books' => $books]);
    }

    /**
     * I should add to this a function to filter out books that are not allowed.
     * 
     * @return BookEntity
     */
    private function _getBooks($bid = 0) {
        //display a book interface.
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();

        // add select and from params
        $qb->select('u')
                ->from('PaustianBookModule:BookEntity', 'u');
        if ($bid > 0) {
            $qb->where('(u.bid = ?1)')
                    ->setParameters([1 => $bid]);
        }
        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $books = $query->getResult();
        return $books;
    }

    private function _getChapters($bid = -1, $order = false) {
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();

        // add select and from params
        $qb->select('u')
                ->from('PaustianBookModule:BookChaptersEntity', 'u');

        if ($bid > - 1) {
            $qb->where('(u.bid = ?1)')
                    ->setParameters([1 => $bid]);
        }
        if ($order) {
            $qb->orderBy('u.number', 'ASC');
        }
        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $chapters = $query->getResult();
        return $chapters;
    }

//add the ability to select what you need. I don't need all the contents some time.
    private function _getArticles($cid = -1, $order = false, $content = false) {
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();

        //if content is true, grab all the content
        if($content){
            $qb->select('u')
                ->from('PaustianBookModule:BookArticlesEntity', 'u');
        } else {
            $fields = array('u.title', 'u.aid', 'u.number');
            $qb->select($fields)
                ->from('PaustianBookModule:BookArticlesEntity', 'u');
        }
            
        if ($cid > - 1) {
            $qb->where('(u.cid = ?1)')
                    ->setParameters([1 => $cid]);
        }
        if ($order) {
            $qb->orderBy('u.number', 'ASC');
        }
        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $articles = $query->getResult();
        return $articles;
    }

    /**
     * @Route("/modifychapter")
     * @param Request $request
     * @return type
     */
    public function modifychapterAction(Request $request) {

        $chapters = $this->_getChapters();
        $em = $this->getDoctrine()->getManager();
        $bookNames = array();
        foreach ($chapters as $chapter) {
            if ($chapter->getBid() === 0) {
                $bookNames[] = __('Unassigned');
            } else {
                $book = $this->_getBooks($chapter->getBid());
                //since we are getting one book, it is the first in the array.
                $bookNames[] = $book[0]->getName();
            }
        }
        return $this->render('PaustianBookModule:Admin:book_admin_modifychapter.html.twig', ['chapters' => $chapters,
                    'books' => $bookNames]);
    }

    /**
     * @Route("/modifyarticle")
     * 
     * Create an interface for picking the article you want to edit
     * @param Request $request
     * @return type
     */
    public function modifyarticleAction(Request $request, BookArticlesEntity $article = null) {
        //build an organization of the book
        $books = $this->_buildtoc();
        
        return $this->render('PaustianBookModule:Admin:book_admin_modifyarticle.html.twig', ['books' => $books]);
    }

    private function _buildtoc($bid = 0, &$chapterids="") {
        //get the list of books
        $booksEnts = $this->_getBooks($bid);

        $books = array();
        foreach ($booksEnts as $bookEnt) {
            $book = array();
            //get list of chapters in the book, ordered by number (that is the the true is for)
            $chapterEnt = $this->_getChapters($bookEnt->getBid(), true);
            $chapters = array();
            $articles = array();
            foreach ($chapterEnt as $chapterEnt) {
                $chapter = array();
                //get all the articles in this chapter, ordered by number
                $articles = $this->_getArticles($chapterEnt->getCid(), true, false);
                $chapter['articles'] = $articles;
                $chapter['name'] = $chapterEnt->getName();
                $chapter['number'] = $chapterEnt->getNumber();
                $chapter['cid'] = $chapterEnt->getCid();
                $chapters[] = $chapter;
                $chapterids .= $chapter['cid'] . ",";
            }
            $book['chapters'] = $chapters;
            $book['bid'] = $bookEnt->getBid();
            $book['name'] = $bookEnt->getName();
            $books[] = $book;
        }
        //now grab all the articles that are undeclared
        $articleEnt = $this->_getArticles(0, false, false);
        $chapUndcl = array();
        $chapUndcl[0]['articles'] = $articleEnt;
        $chapUndcl[0]['name'] = 'Unassociated';
        $chapUndcl[0]['number'] = -1;
        $chapUndcl[0]['cid'] = 0;
        $bookUndl = array();
        $bookUndl['chapters'] = $chapUndcl;
        $bookUndl['bid'] = 0;
        $bookUndl['name'] = 'Unassociated';
        $books['undcl'] = $bookUndl;
        //tag the Unassociated "id" on the chapter ids
        $chapterids .= "0";
        return $books;
       /* $retArray = array();
        $bookArray = array();
        $chapArray = array();
        //grab all the books
        $books = $this->_getBooks($bid);
        // now walk the books and harvest the chapters and the articles.
        foreach($books as $book){
            $bookArray['name'] = $book->getName();
            $bookArray['bid'] = $book->getBid();
            $chapters = $this->_getChapters($book->getBid(), true);
            foreach($chapters as $chapter){
                $chapArray['articles'] = $this->_getArticles($chapter->getCid(), true, false);
                $chapArray['name'] = $chapter->getName();
                $chapArray['cid'] = $chapter->getCid();
                $chapArray['number'] = $chapter->getNumber();
                $bookArray[] = $chapArray;
                $chapArray = array();
            }
            $retArray[] = $bookArray;
            $bookArray = array();
        }
        return $retArray;*/
    }

    /**
     * @Route("/modifyfigure/{figure}")
     * @param Request $request
     * @return type
     */
    public function modifyfigureAction(Request $request, BookFiguresEntity $figure = null) {
        return $this->redirect($this->generateUrl('paustianbookmodule_admin_edit'));
    }

    /**
     * @Route("/modifyglossary/{gloss}")
     * @param Request $request
     * @return type
     */
    public function modifyglossaryAction(Request $request, BookGlossEntity $gloss = null) {
        return $this->redirect($this->generateUrl('paustianbookmodule_admin_edit'));
    }

    /**
     * @Route("/modifyuserdata/{udata}")
     * @param Request $request
     * @return type
     */
    public function modifyUserDataAction(Request $request, BookUserDataEntity $udata = null) {
        return $this->redirect($this->generateUrl('paustianbookmodule_admin_edit'));
    }

    private function _generate_chapter_menu() {
        //get the complete list of books
        $books = ModUtil::apiFunc('Book', 'user', 'getall', array('startnum' => 1));

        if ($books == false) {
            //if we dont' have a book, then you
            //cannot have chapters
            return LogUtil::addWarningPopup($this->__('You have to create a book before you can create a chapter or an article'));
        }

        $chapters = array();
        //$i and $j are counters that verify that there is at least one chatper in
        //one book. If no chapters have been created, then after the loop
        //$j and $i will be equal. In that case, do not allow the funciton to
        //continue.
        $i = $j = 0;
        //get all the chapters for each book using the bids
        //we can get this from the $books array
        foreach ($books as $book_item) {
            $bid = $book_item['bid'];
            //grab all the chapters for this book
            $chap_info = ModUtil::apiFunc('Book', 'user', 'getallchapters', array('bid' => $bid));
            //check to make sure they are legitimate. The function will
            //send back false if it fails
            $j++;
            if ($chap_info == false) {
                $i++;
            }
            //we store this information for use later in
            //making our form. Each array item matches a book.
            //I could probably just put this down in the bottom
            //and write the form out, but this is a bit cleaner.
            $chapters[] = $chap_info;
        }
        //there are no chapters
        if ($j == $i) {
            return LogUtil::addErrorPopup($this->__('There are no chapters.'));
        }

        // Start the table
        $i = 0;

        $this->view->assign('books', $books);

        $chap_menus = array();
        foreach ($books as $book_item) {
            $menuItem = array();
            foreach ($chapters[$i] as $chap_item) {
                $menuItem[$chap_item['cid']] = $chap_item['name'];
            }
            $i++;
            $chap_menus[] = $menuItem;
        }
        return $chap_menus;
    }

    /**
     * @Route("/export")
     * I may want to add a variable to choose which chapter.
     * @param Request $request
     * @return type
     */
    public function exportAction(Request $request) {

        return $this->redirect($this->generateUrl('paustianbookmodule_admin_edit'));
        /*
          $ret_url = ModUtil::url('book', 'admin', 'main');
          if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
          return LogUtil::registerPermissionError($ret_url);
          }

          //get the complete list of books
          $books = ModUtil::apiFunc('Book', 'user', 'getall', array('startnum' => 1));

          if ($books == false) {
          //if we dont' have a book, then you
          //cannot have chapters
          return LogUtil::addWarningPopup($this->__('There are no books to export'), null, $ret_url);
          }

          $chapters = array();
          //$i and $j are counters that verify that there is at least one chatper in
          //one book. If no chapters have been created, then after the loop
          //$j and $i will be equal. In that case, do not allow the funciton to
          //continue.
          $i = $j = 0;
          //get all the chapters for each book using the bids
          //we can get this from the $books array
          foreach ($books as $book_item) {
          $bid = $book_item['bid'];

          //grab all the chapters for this book
          $chap_info = ModUtil::apiFunc('Book', 'user', 'getallchapters', array('bid' => $bid));
          //check to make sure they are legitimate. The function will
          //send back false if it fails
          $j++;
          if ($chap_info == false) {
          $i++;
          }
          //we store this information for use later in
          //making our form. Each array item matches a book.
          //I could probably just put this down in the bottom
          //and write the form out, but this is a bit cleaner.
          $chapters[] = $chap_info;
          }
          //there are no chapters to delete
          if ($j == $i) {
          return LogUtil::addWarningPopup($this->__('There are no chapters to export.'), null, $ret_url);
          }

          // Start the table
          $i = 0;

          $this->view->assign('books', $books);

          $chap_menus = array();
          foreach ($books as $book_item) {
          $menuItem = array();
          foreach ($chapters[$i] as $chap_item) {
          // Security check
          if (SecurityUtil::checkPermission('Book::Chapter', "$book_item[bid]::$chap_item[cid]", ACCESS_EDIT)) {
          $menuItem[$chap_item['cid']] = $chap_item['name'];
          }
          }
          $i++;
          $chap_menus[] = $menuItem;
          }

          $this->view->assign('chapters', $chap_menus);

          return $this->view->fetch('book_admin_doexport.tpl');
         * */
    }

    /**
     * @Route("deletechapter/{chapter}")
     * 
     * @param Request $request
     * @param BookChaptersEntity $chapter
     * @return type
     * @throws AccessDeniedException
     */
    public function deletechapterAction(Request $request, BookChaptersEntity $chapter = null) {
        $response = $this->redirect($this->generateUrl('paustianbookmodule_admin_modifychapter'));
        if ($chapter == null) {
            //you want the edit interface, which has a delete option.
            return $response;
        }

        if (!SecurityUtil::checkPermission('::Chapter', "::" . $chapter->getCid(), ACCESS_DELETE)) {
            throw new AccessDeniedException($this->__("You do not have permission to delete that chapter."));
        }


        //I need to walk the articles and remove any reference to this book
        //Set the chapters to 0
        $articles = $this->_getArticles($chapter->getCid(), false, false);
        foreach ($articles as $article) {
            $articles->setCid(0);
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($chapter);
        $em->flush();
        $this->addFlash('status', __('Chapter Deleted.'));
        return $response;
    }

    /**
     * @Route("deletearticle/{article}")
     * @param Request $request
     * @param BookArticlesEntity $article
     * @return RedirectResponse
     */
    public function deletearticleAction(Request $request, BookArticlesEntity $article = null) {

        $response = $this->redirect($this->generateUrl('paustianbookmodule_admin_modifyarticle'));
        if (null === $article) {
            //you want the edit interface, which has a delete option.
            return $response;
        } else {
            if (!SecurityUtil::checkPermission('book::', $article->getBid() . "::" . $article->getCid(), ACCESS_DELETE)) {
                throw new AccessDeniedException($this->__("You do not have permission to delete this article."));
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();
        $this->addFlash('status', __('Article Deleted.'));
        //I may need to add a notify hook response here. I think I can add this to the 
        //template however.
        return $response;
    }

    /**
     * @Route("/deletefigure/{figure}")
     * @param Request $request
     * @param BookFiguresEntity $figure
     * @return RedirectResponse
     * @throws AccessDeniedException
     */
    public function deletefigureAction(Request $request, BookFiguresEntity $figure) {
        $response = $this->redirect($this->generateUrl('paustianbookmodule_admin_modifyfigure'));
        if ($figure == null) {
            if (!SecurityUtil::checkPermission('book::', "::", ACCESS_DELETE)) {
                throw new AccessDeniedException($this->__("You do not have permission to delete figures."));
            }
            //you want the edit interface, which has a delete option.
            return $response;
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($figure);
        $em->flush();
        $this->addFlash('status', __('Figure deleted.'));
        return $response;
    }

    /**
     * @Route("deleteglossary/{gloss}")
     * @param Request $request
     * @param BookGlossEntity $gloss
     * @return type
     * @throws AccessDeniedException
     */
    public function deleteglossaryAction(Request $request, BookGlossEntity $gloss) {
        if (!SecurityUtil::checkPermission('book::', "::", ACCESS_DELETE)) {
            throw new AccessDeniedException($this->__("You do not have permission to delete that glossary item."));
        }
        $response = $this->redirect($this->generateUrl('paustianbookmodule_admin_modifyglossary'));
        if ($gloss == null) {
            //you want the edit interface, which has a delete option.
            return $response;
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($gloss);
        $em->flush();
        $this->addFlash('status', __('Glossary item deleted.'));
        return $response;
    }

    /**
     * @Route("/modifyconfig")
     * @param Request $request
     * @return type
     */
    public function modifyconfigAction(Request $request) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        if (ModUtil::getvar('Book', 'securebooks')) {
            $this->view->assign('issecure', "checked");
        } else {
            $this->view->assign('issecure', '');
        }
        return $this->view->fetch('book_admin_modifyconfig.tpl');
    }

    /**
     * This is a standard function to update the configuration parameters of the
     * module given the information passed back by the modification form
     */
    public function updateconfig() {
        return true;
    }

    /**
     * modifyaccess
     *
     * Change the access to the book. If this is turned on, then only one person per username is allowed to
     * access the book at a time. This prevents people from cheating.
     */
    public function modifyaccess() {
// Security check
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        $secure = FormUtil::getPassedValue('secure', isset($args['secure']) ? $args['secure'] : null);
        ModUtil::setVar('Book', 'securebooks', $secure == "makesecure");

        return new RedirectResponse(ModUtil::url('book', 'admin', 'modifyconfig'));
    }

    /**
     * @Route("/arrangearticlesAction")
     * @param Request $request
     * @return type
     */
    public function arrangearticlesAction(Request $request) {
        
        $books = $this->_buildtoc(0, $chapterids);
        
        return $this->render('PaustianBookModule:Admin:book_admin_arrangearticles.html.twig', ['books' => $books,
                    'chapterids' => $chapterids]);
    }

    /**
     * @Route("/savearrangement")
     * @param Request $request
     * @return type
     */
    public function savearrangementAction(Request $request) {
        $chapterIds = $request->request->get('chapterids');
        $chapterId_array = explode(',', $chapterIds);
        //walk each chapter and associate the articles in it's box with it
        $em = $this->getDoctrine()->getManager();
        foreach ($chapterId_array as $chapterId) {
            $chapter = $em->find('PaustianBookModule:BookChaptersEntity', $chapterId);
            if (!isset($chapter)) {
                $bookId = 0;
            } else {
                $bookId = $chapter->getBid();
            }
            $order = $request->request->get('order_' . $chapterId);
            parse_str($order, $matches);
            $artIds = $matches['art'];
            //we put this in to make sure we don't try to persist 
            //an empty set.
            if (count($artIds) < 1) {
                continue;
            }
            $oldArticle = null;
            $number = 1;
            //this is for the most part done. I do have the links to next and previous being a little funky
            //  they should not link to chapters outside their own book and they are right now.
            foreach ($artIds as $aid) {
                $article = $em->find('PaustianBookModule:BookArticlesEntity', $aid);
                $article->setNumber($number);
                $article->setCid($chapterId);
                $article->setBid($bookId);
                if ($oldArticle != null) {
                    if ($bookId == 0) {
                        $article->setPrev(0);
                        $oldArticle->setNext(0);
                    } else {
                        $article->setPrev($oldArticle->getAid());
                        $oldArticle->setNext($aid);
                    }
                    $em->persist($oldArticle);
                } else {
                    //if $oldarticle is null, we are at the first article
                    //and we need to set the prev link to 0
                    $article->setPrev(0);
                }
                $number++;
                $oldArticle = $article;
            }
            //we dropped out of the chapter, 
            //zero out the next and then save
            $oldArticle->setNext(0);
            if ($number == 1) {
                $oldArticle->setPrev(0);
            }
            $em->persist($oldArticle);
            $oldArticle = null;
        }
        $em->flush();
        return $this->redirect($this->generateUrl('paustianbookmodule_admin_arrangearticles'));
    }

    /**
     * export
     *
     * book_admin_export
     *
     * Export a chapter in a format for editing. Right now this basically accumilates
     * the html and spits it out to be edited. As long as you don't mess with the
     * tags, it should import correctly.
     *
     */
    public function exportchapter($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Book', 'admin', 'view'));
        }

//get the book id to process
        $bid = FormUtil::getPassedValue('book', isset($args['book']) ? $args['book'] : null);
        $inline_figures = FormUtil::getPassedValue('inline', isset($args['inline']) ? $args['inline'] : null);
        $chap_to_get = 'chapter_' . $bid;
        $cid = FormUtil::getPassedValue($chap_to_get, isset($args[$chap_to_get]) ? $args[$chap_to_get] : null);
//The presented text is almost xhtml compliant. I need to change the inline
//figures which are with curly braces and then strip the glossary entries and it's
//all set. I should also add a funciton that adds the references to the figures if a switch is
//set. This way I can use it for publishing the text, instead of having it go through the browser
//which hoses it up. I also need to test this xhtml file through prince pdfs
// Create output object
//there are still problems with this. it may be better to
//do a separate export calling the user function if you want the figures inlined. Try that.
//
        //replace the glossary entries
// $text = preg_replace('|<a class="glossary" href=.*?\'\)">(.*?)</a>|', '<a class="glossary">$1</a>' ,$text); 
        $text = "";
        if ($inline_figures == 'on') {
//process the chapter adding links to the figures.
            $text = ModUtil::func('Book', 'user', 'displaychapter', array('cid' => $cid));
            $text = preg_replace('|<a class=\"glossary\".*?\'\)\">(.*?)</a>|', '$1', $text);
        } else {

            $text = $this->exportchapter_noinline(array('cid' => $cid));
        }
//remove amersands in urls
//Author: TImothy Paustian date: August 1 2010
//This was a tricky problem. I finally settled on a double search function
//we first pick out all the urls, cause there is there the problem is,
//I don't use & in my text. Now that the & is isolated, I can then
//do another call to preg_replace. The tricky part was that the browser was
//reading the entity and fixing it, so I have to add a
//second amp; to get it to read right out of the form.
        $text = preg_replace_callback('|href="(.*?)"|', "Book_Controller_Admin::url_replace_func", $text);

//Format the text for display. The big error this took care of was 
//making sure entitities get displayed right. ° is &deg; µ is &micro;, etc. 
        $text = DataUtil::formatForDisplay($text);
        $book = ModUtil::apiFunc('Book', 'user', 'get', array('bid' => $bid));

        $this->view->assign('export_text', $text);
        $this->view->assign('name', $book['name']);
        $text = $this->view->fetch('book_admin_export.tpl');
        return $text;
    }

    public function exportchapter_noinline($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

//get the chapter id
        $cid = FormUtil::getPassedValue('cid', isset($args['cid']) ? $args['cid'] : null);

        if (!isset($cid)) {
            return LogUtil::addErrorPopup($this->__('Argument error in exportchapter_noinline.'));
        }

        $chapter = ModUtil::apiFunc('Book', 'user', 'getchapter', array('cid' => $cid));


        $articles = ModUtil::apiFunc('Book', 'user', 'getallarticles', array('cid' => $cid,
                    'get_content' => true));
        $this->view->assign('chapter', $chapter);
        $num_arts = count($articles);
        for ($i = 0; $i < $num_arts; $i++) {
            $articles[$i]['contents'] = preg_replace('/\&(.*?);/', '&amp;$1;', $articles[$i]['contents']);
        }
        $this->view->assign('articles', $articles);

        $return_text = $this->view->fetch('book_admin_chapter_xml.tpl');
//we need to clean out the glossary and Figure notation stuff.
//it will be brought right back in on import
//This stuff really still screws up the book.
//I guess we are just going to have to leave it.

        /* $pattern = '/<a class="glossary" href.*?\'\)">(.*?)<\/a>/';
          $replacement = '<a class="glossary">$1</a>';
          $return_text = preg_replace($pattern, $replacement, $return_text);

          $pattern = '/<a.*?>Figure ([0-9]+)-([0-9]+)<\/a>/';
          $replacement = 'Figure $1-$2';
          $return_text = preg_replace($pattern, $replacement, $return_text); */
        $this->view->assign('export_text', $return_text);
        return $return_text;
    }

    /**
     * exportbook
     *
     * book_admin_exportbook
     *
     * Export a book in a format for editing. Right now this basically accumilates
     * the html and spits it out to be edited. As lon as you don't mess with the
     * tags, it should import correctly.
     */
    public function exportbook($args) {
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Book', 'admin', 'view'));
        }

        $bid = FormUtil::getPassedValue('book', isset($args['book']) ? $args['book'] : null);

        if (!isset($bid)) {
            return LogUtil::addErrorPopup($this->__('Argument error in exportbook.'));
        }

        $book = ModUtil::apiFunc('Book', 'user', 'get', array('bid' => $bid));

        $chapters = ModUtil::apiFunc('Book', 'user', 'getallchapters', array('bid' => $bid));


        $export_text = "";
//now cycle through each chapter delimiting its boundries.
        foreach ($chapters as $chap_item) {
//flank the return text with chapter xml
            $export_text = $export_text . "<br />" . ModUtil::apiFunc('Book', 'admin', 'exportchapter', array('cid' => $chap_item['cid']));
        }

        $this->view->assign('export_text', $export_text);
        $this->view->assign('name', $book['name']);
        return $this->view->fetch('book_admin_export.tpl');
    }

    /**
     * book_admin_doimport
     *
     * we just provide a text area for the modified chatper to
     * be added to.
     */
    public function doimport() {
// Security check
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        return $this->view->fetch('book_admin_import.tpl');
    }

    /**
     * @Route("/import")
     *
     * Import a chapter into the textbook. This should take the exported text
     * and reprocess it. One problem I may need to solve is munged text.
     * I will have to do some serious checking for missing params, and if not
     * there, unwind the whole process.
     */
    public function importAction($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

//security check
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Book', 'admin', 'view'));
        }

        $in_data = FormUtil::getPassedValue('chap_to_import', isset($args['chap_to_import']) ? $args['chap_to_import'] : null);
        if (!isset($in_data)) {
            return LogUtil::addWarningPopup($this->__('No data to import.'));
        }

        $matches = array();
        $pattern = '|<!--\(bookname\)(.*?)\(\/bookname\)-->|';
        preg_match($pattern, $in_data, $matches);
        $name = $matches[1];
//grab bid
        $pattern = '|<!--\(bookid\)([0-9]*?)\(\/bookid\)-->|';
        preg_match($pattern, $in_data, $matches);
        $bid = $matches[1];
//update the book data
        if (!ModUtil::apiFunc('Book', 'admin', 'update', array('bid' => $bid, 'name' => $name))) {
//if update fails, try create
            if (!ModUtil::apiFunc('Book', 'admin', 'create', array('bid' => $bid, 'name' => $name))) {
//set an error message and return false
                SessionUtil::setVar('error_msg', $this->__('Book import failed. Ha ha.'));
            }
        }
        $chap_matches = array();
//now match each section<!
        $pattern = '|<!--\(chapter\)-->(.*)<!--\(/chapter\)-->|s';
        preg_match_all($pattern, $in_data, $chap_matches, PREG_PATTERN_ORDER);

        foreach ($chap_matches[1] as $chap_data) {

//grab the title of the imported chapter
            $pattern = '|<!--\(chapname\)(.*?)\(\/chapname\)-->|';
            preg_match($pattern, $chap_data, $matches);
            $chap_title = $matches[1];

//grab the chapter id
            $pattern = '|<!--\(chapid\)([0-9]*)\(\/chapid\)-->|';
            preg_match($pattern, $chap_data, $matches);
            $cid = $matches[1];
//grab chapter number
            $pattern = '|<!--\(chapnumber\)([0-9]*)\(\/chapnumber\)-->|';
            preg_match($pattern, $chap_data, $matches);
            $number = $matches[1];
            $pattern = '|<!--\(bookid\)([0-9]*?)\(\/bookid\)-->|';
            preg_match($pattern, $chap_data, $matches);
            $bid = $matches[1];
// Security check
            if (!SecurityUtil::checkPermission('Book::Chapter', "$book[bid]::$chapter[cid]", ACCESS_EDIT)) {
                return LogUtil::registerPermissionError();
            }
//update the chapter data
            if (!ModUtil::apiFunc('Book', 'admin', 'updatechapter', array('bid' => $bid, 'name' => $chap_title, 'number' => $number, 'cid' => $cid))) {
//if update fails, try create
                if (!ModUtil::apiFunc('Book', 'admin', 'createchapter', array('bid' => $bid, 'name' => $chap_title, 'number' => $number, 'cid' => $cid))) {
//set an error message and return false
                    SessionUtil::setVar('error_msg', $this->__('Chapter update failed. Ha ha.'));
                }
            }
//debugging code do not remove
//return "chap title $chap_title <br> chap id: $cid <br> chap number:$number
//		<br>book id: $bid <br>" ;
//now match each section
            $pattern = '|<!--\(section\)-->(.*?)<!--\(\/section\)-->|s';
            preg_match_all($pattern, $chap_data, $matches, PREG_PATTERN_ORDER);

            foreach ($matches[1] as $match_item) {
//extract the data for each article
//and then update it.
//<p class="art_aid}1</p>
                $pattern = '|<!--\(artartid\)([0-9]*)\(\/artartid\)-->|';
                preg_match($pattern, $match_item, $art_match);
                $aid = $art_match[1];

//{art_counter}78{/p>
                $pattern = '|<!--\(artcounter\)([0-9]*)\(\/artcounter\)-->|';
                preg_match($pattern, $match_item, $art_match);
                $art_counter = $art_match[1];

//{art_lang}eng{/p>
                $pattern = '|<!--\(artlang\)(.*?)\(\/artlang\)-->|';
                preg_match($pattern, $match_item, $art_match);
                $art_lang = $art_match[1];

//{art_next}2{/p>
                $pattern = '|<!--\(artnext\)([0-9]*)\(\/artnext\)-->|';
                preg_match($pattern, $match_item, $art_match);
                $art_next = $art_match[1];

//{art_prev}0{/p>
                $pattern = '|<!--\(artprev\)([0-9]*)\(\/artprev\)-->|';
                preg_match($pattern, $match_item, $art_match);
                $art_prev = $art_match[1];

//{aid}1{/p>
                $pattern = '|<!--\(artnumber\)([0-9]*)\(\/artnumber\)-->|';
                preg_match($pattern, $match_item, $art_match);
                $aid = $art_match[1];

//{div class="contents}....{/div>
                $pattern = '|<!--\(content\)-->(.*?)<!--\(\/content\)-->|s';
                preg_match($pattern, $match_item, $art_match);
                $art_content = $art_match[1];

//{h2 class="art_title} Microbes in the environment{/h2>
                $pattern = '|<!--\(arttitle\)(.*?)\(\/arttitle\)-->|';
                preg_match($pattern, $match_item, $art_match);
                $art_title = $art_match[1];
//Note, check this out first by just printing it.
//Now that we have all the data, update the article

                /* Debugging code, do not remove
                 * $ret_text .= "<p>aid: $aid<br>art_counter: $art_counter<br>art_lang:$art_lang<br>" .
                  "art_next:$art_next<br>art_prev:$art_prev<br>aid:$aid<br>" .
                  "art_title: $art_title<br>art_contents: $art_content<br>";
                 */

                if (!ModUtil::apiFunc('Book', 'admin', 'updatearticle', array('aid' => $aid,
                            'bid' => $bid,
                            'title' => $art_title,
                            'contents' => $art_content,
                            'cid' => $cid,
                            'lang' => $art_lang,
                            'next' => $art_next,
                            'prev' => $art_prev,
                            'aid' => $aid))) {
// failure
//try creating it then
                    if (!ModUtil::apiFunc('Book', 'admin', 'createarticle', array('aid' => $aid, 'bid' => $bid, 'title' => $art_title, 'content' => $art_content, 'cid' => $cid, 'lang' => $art_lang, 'next' => $art_next, 'prev' => $art_prev, 'aid' => $aid))) {
                        /* print "<p>aid: $aid<br>art_counter: $art_counter<br>art_lang:$art_lang<br>" .
                          "art_next:$art_next<br>art_prev:$art_prev<br>aid:$aid<br>" .
                          "art_title: $art_title<br>art_contents: $art_content<br>";die; */
                        $prev_error = pnSessionGetVar('error_msg');
                        SessionUtil::setVar('errormsg', $this->__('Book import failed.') . $prev_error);
                        return false;
                    }
                }
            }
        }
//if we get here, we succeded
        LogUtil::addStatusPopup($this->__('Import succedeed'));

//We now need to process the entire book, so send it along
        ModUtil::apiFunc('Book', 'admin', 'processalldocuments', array('bid' => $bid));
// Return
        return new RedirectResponse(ModUtil::url('book', 'admin', 'doimport'));
    }

    public function dolistbookfigures() {
// Security check - important to do this as early as possible to avoid
// potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $bookItems = ModUtil::apiFunc('Book', 'user', 'getall', array('startnum' => 1));

        if ($bookItems == 0) {
//if we dont' have a book, then you
//cannot have chapters
            return LogUtil::addWarningPopup($this->__('You have no books so you cannot list the figures'));
        }
        $this->view->assign('books', $bookItems);

        return $this->view->fetch('book_admin_dolistfigure.tpl');
    }

    /**
     * * @Route("/listfigures")
     * @param Request $request
     * @return type
     */
    public function listfiguresAction(Request $request) {
        return $this->redirect($this->generateUrl('paustianbookmodule_admin_edit'));
        /* if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
          return LogUtil::registerPermissionError();
          }

          if (!SecurityUtil::confirmAuthKey()) {
          return LogUtil::registerAuthidError(ModUtil::url('Book', 'admin', 'view'));
          }
          $bid = FormUtil::getPassedValue('bid', isset($args['bid']) ? $args['bid'] : null);
          $button = FormUtil::getPassedValue('submit');

          $this->view->caching = false;

          // The user API function is called
          $figData = ModUtil::apiFunc('Book', 'user', 'getallfigures', array('bid' => $bid));

          if ($figData == false) {
          LogUtil::addWarningPopup($this->__('There are no figures to list'));
          return ModUtil::url('book', 'admin', 'dolistbookfigures');
          }


          $this->view->assign('figData', $figData);
          $ret_text = "";
          if ($button == 'listpaths') {
          $ret_text = $this->view->fetch('book_admin_listpaths.tpl');
          } else {
          $ret_text = $this->view->fetch('book_admin_listigures.tpl');
          }
          // Return the output that has been generated by this function
          return $ret_text; */
    }

    public function modifyimagepaths($args) {
//only admins can do this
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError(ModUtil::url('Book', 'admin', 'view'));
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Book', 'admin', 'view'));
        }

        $fids = FormUtil::getPassedValue('fid');
        $img_paths = FormUtil::getPassedValue('img_link');

//we now have the figure paths and the ones we want to change. Walk through the list and change each one
        foreach ($fids as $fid) {
            $new_path = $img_paths[$fid];
            $figure = ModUtil::apiFunc('Book', 'user', 'getfigure', array('fid' => $fid));

            $result = ModUtil::apiFunc('Book', 'admin', 'updatefigure', array('fid' => $figure['fid'],
                        'fig_number' => $figure['fig_number'],
                        'title' => $figure['title'],
                        'content' => $figure['content'],
                        'img_link' => $new_path,
                        'chap_number' => $figure['chap_number'],
                        'perm' => $figure['perm'],
                        'bid' => $figure['bid']));
// Call apiupdate to do all the work
            if ($result) {
// Success
                LogUtil::addStatusPopup($this->__('The figure was updated.'));
            } else {
                LogUtil::addErrorPopup($this->__('Update of figure failed.'));
                return false;
            }
        }

        return new RedirectResponse(ModUtil::url('book', 'admin', 'dolistbookfigures'));
    }

    public function choose_verify_url() {

//only admins can do this
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError(ModUtil::url('Book', 'admin', 'view'));
        }
//get the complete list of books
        $books = ModUtil::apiFunc('Book', 'user', 'getall', array('startnum' => 1));

        if ($books == false) {
//if we dont' have a book, then you
//cannot have chapters
            return LogUtil::addWarningPopup($this->__('There are no urlS to check since you have not created a book.'));
        }

        $chapters = array();
//$i and $j are counters that verify that there is at least one chatper in
//one book. If no chapters have been created, then after the loop
//$j and $i will be equal. In that case, do not allow the funciton to
//continue.
        $i = $j = 0;
//get all the chapters for each book using the bids
//we can get this from the $books array
        foreach ($books as $book_item) {
            $bid = $book_item['bid'];
//grab all the chapters for this book
            $chap_info = ModUtil::apiFunc('Book', 'user', 'getallchapters', array('bid' => $bid));
//check to make sure they are legitimate. The function will
//send back false if it fails
            $j++;
            if ($chap_info == false) {
                $i++;
            }
//we store this information for use later in
//making our form. Each array item matches a book.
//I could probably just put this down in the bottom
//and write the form out, but this is a bit cleaner.
            $chapters[] = $chap_info;
        }
//there are no chapters to delete
        if ($j == $i) {
            return LogUtil::addWarningPopup($this->__('There are no chapters, so there are no urlS to check.'));
        }

// Start the table
        $i = 0;

        $this->view->assign('books', $books);

        $chap_menus = array();
        foreach ($books as $book_item) {
            $menuItem = array();
            foreach ($chapters[$i] as $chap_item) {
                $menuItem[$chap_item['cid']] = $chap_item['name'];
            }
            $i++;
            $chap_menus[] = $menuItem;
        }

        $this->view->assign('chapters', $chap_menus);

        return $this->view->fetch('book_admin_verify_chapter.tpl');
    }

    public function verify_urls($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Book', 'admin', 'dodelete'));
        }
        $bid = FormUtil::getPassedValue('bid', isset($args['bid']) ? $args['bid'] : null);
        $chap_to_get = 'chapter_' . $bid;
        $cid = FormUtil::getPassedValue($chap_to_get, isset($args[$chap_to_get]) ? $args[$chap_to_get] : null);

        $url_table = array();
        if (isset($cid)) {
            $chapter_info = ModUtil::apiFunc('Book', 'user', 'getchapter', array('cid' => $cid));
            $articles = ModUtil::apiFunc('Book', 'user', 'getallarticles', array('cid' => $cid));

            foreach ($articles as $article_item) {
// Security check
                if (SecurityUtil::checkPermission('Book::', "$book[bid]::$chapter_info[cid]", ACCESS_EDIT)) {
                    $this->buildtable($article_item['contents'], $url_table, $chapter_info['number'], $article_item['number']);
                }
            }
        }
        $this->view->assign('chapter_num', $chapter_info['number']);
        $this->view->assign('url_table', $url_table);

        return $this->view->fetch('book_admin_verify_urls.tpl');
    }

    function buildtable($content, &$url_table, $chap_no, $article_no) {
        $matches = array();
        $url_row = array();
        $new_urls = array();
        preg_match_all("/<a.*?href=\"(.*?)\"/", $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $url_item) {
            $url_row['url'] = $url_item[1];
            $url_row['chap_no'] = $chap_no;
            $url_row['article_no'] = $article_no;
            $new_urls[] = $url_row;
        }
        $new_urls = $this->checkurls($new_urls);
        $url_table = array_merge($url_table, $new_urls);
    }

//include 'get_headers.php';

    /**
     * @Route("/checkurls")
     * 
     * checkurls
     * parameters
     * 	$args=>cid	the chapter id
     *  $args=>bid	the book id
     *
     * Given a book id or chapter id, work through the articles in the book and find each
     * url. Then check them all. When the checking is done, a report lists all the bad urls
     * and their article location. Note, this does not list internal links, since those return
     * without error due to the way zikula is set up.
     */
    public function checkurlsAction(Request $request, $urls = null) {
//the url to the current server
        $baseurl = ModUtil::getBaseDir();
        $i = 0;
        foreach ($urls as $items) {
//check to see if it is a valid url
            if (!$this->is_url($items['url'])) {
                if (preg_match("/^\\//", $items['url'])) {
//root directory. Append the host and stop
//remove the first /
                    $items['url'] = pnServerGetProtocol() . "://" . pnServerGetHost() . $items['url'];
                } else {
//relative link
                    $items['url'] = $baseurl . trim($items['url'], "/");
                }
            }
//this is an internal link
            if (strpos(strtolower($items['url']), strtolower($baseurl)) !== FALSE) {
//check it internally
//first parse it.
                $url_array = parse_url($items['url']);
                $arr_query = array();
                $args = explode('&', $url_array['query']);
                foreach ($args as $arg) {
                    $parts = explode('=', $arg);
                    $arr_query[$parts[0]] = $parts[1];
                }
                $modname = $arr_query['module'];
                if ($arr_query['type'] != "") {
                    $type = $arr_query['type'];
                } else {
                    $type = 'user';
                }
                if ($arr_query['func'] != "") {
                    $func = $arr_query['func'];
                } else {
                    $func = 'main';
                }
//check to see if we can actually call this function
                if (ModUtil::getCallable($modname, $type, $func)) {
                    $urls[$i]['present'] = 1;
                } else {
                    $urls[$i]['present'] = -1;
                }
            } else {
                $urls[$i]['present'] = $this->check_http_link($items);
            }
            $i++;
        }

        return $urls;
    }

    function is_url($url) {
        if (!preg_match('/^http\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+/i', $url)) {
            return false;
        } else {
            return true;
        }
    }

    function check_http_link($inItem) {
        if (!$this->is_valid_url($inItem['url'])) {
            return -1;
        } else {
            return 1;
        }
    }

    function is_valid_url($url) {
        $url = @parse_url($url);

        if (!$url) {
            return false;
        }

        $url = array_map('trim', $url);
        $url['port'] = (!isset($url['port'])) ? 80 : (int) $url['port'];
        $path = (isset($url['path'])) ? $url['path'] : '';

        if ($path == '') {
            $path = '/';
        }

        $path .= ( isset($url['query']) ) ? "?$url[query]" : '';

        if (isset($url['host']) AND $url['host'] != gethostbyname($url['host'])) {
            if (PHP_VERSION >= 5) {
                $headers = get_headers("$url[scheme]://$url[host]:$url[port]$path");
            } else {
                $fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);

                if (!$fp) {
                    return false;
                }
                fputs($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
                $headers = fread($fp, 128);
                fclose($fp);
            }
            $headers = ( is_array($headers) ) ? implode("\n", $headers) : $headers;
            return (bool) preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers);
        }
        return false;
    }

    /**
     * @Route("/checkstudentdefs")
     * 
     * @param Request $request
     * @return boolean
     * Students can request words to be defined. These will appear as words with empty definitions.
     * This routine will find all empty definitions in the glossary and then display them to the author.
     * The author can then define them.
     *
     *
     */
    public function checkstudentdefsAction(Request $request) {
        return $this->redirect($this->generateUrl('paustianbookmodule_admin_edit'));
        /*
          if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
          LogUtil::registerPermissionError(ModUtil::url('Book', 'admin', 'view'));
          return true;
          }

          $empty_defs = ModUtil::apiFunc('Book', 'admin', 'getemptyglossaryitems');
          //put all the gids into an array, we use this for grabbing them later
          $gids = array();
          foreach ($empty_defs as $def_item) {
          $gids[] = $def_item['gid'];
          }

          $this->view->assign('gids', DataUtil::formatForDisplayHTML(serialize($gids)));
          $this->view->assign('empty_defs', $empty_defs);

          return $this->view->fetch('book_admin_checkstudent_defs.tpl'); */
    }

    public function modifyglossaryitems($args) {
//only admins can do this
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            LogUtil::registerPermissionError(ModUtil::url('Book', 'admin', 'view'));
            return true;
        }
//grab the array listing all the gloss ids to be updated
        $gloss_array = FormUtil::getPassedValue('gids', isset($args['gids']) ? $args['gids'] : null);
        $gids = unserialize($gloss_array);

//each item in the table is defined by a combination
//of what it is and the gloss id, farm each one out using
//the array we just grabbed from the form.
        foreach ($gids as $gloss_item) {
            $term = FormUtil::getPassedValue('term_' . $gloss_item, isset($args['term_' . $gloss_item]) ? $args['term_' . $gloss_item] : null);
            $definition = FormUtil::getPassedValue('definition_' . $gloss_item, isset($args['definition_' . $gloss_item]) ? $args['definition_' . $gloss_item] : null);
            $delete = FormUtil::getPassedValue('delete_' . $gloss_item, isset($args['delete_' . $gloss_item]) ? $args['delete_' . $gloss_item] : null);
//first check if we are supposed to delete it
            if ($delete == "on") {
                if (!ModUtil::apiFunc('Book', 'admin', 'deleteglossary', array('gid' => $gloss_item))) {
                    LogUtil::addErrorPopup($this->__('Glossary deletion failed.'), null, ModUtil::url('Book', 'admin', 'checkstudentdefs'));
                    return false;
                }
            } else {

//we don't want to delete, we want to modify
                if (!ModUtil::apiFunc('Book', 'admin', 'updateglossary', array('gid' => $gloss_item, 'term' => $term, 'definition' => $definition))) {
                    LogUtil::addErrorPopup($this->__('Glossary modification failed.'), null, ModUtil::url('Book', 'admin', 'checkstudentdefs'));
                    return false;
                }
            }
        }

//if we get here we were successful,
        LogUtil::addStatusPopup($this->__('Book glossary updated.'));
        return new RedirectResponse(ModUtil::url('book', 'admin', 'checkstudentdefs'));
    }

    /**
     * @Route("/importglossary")
     * @param Request $request
     * @return type
     */
    public function importglossaryAction(Request $request) {
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError(ModUtil::url('Book', 'admin', 'view'));
        }

        return $this->redirect($this->generateUrl('paustianbookmodule_admin_edit'));
    }

    public function doglossaryimport($args) {

//only admins can do this
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError(ModUtil::url('Book', 'admin', 'view'));
        }
        $gloss_data = FormUtil::getPassedValue('gloss_text', isset($args['gloss_text']) ? $args['gloss_text'] : null);
//The glossary text is set up as a xml file
//<glossitem>
//<term>term</term>
//<definition>defnintion</definition>
//</glossitem>
//parse this out into an array
        $matches = array();
        $pattern = '/<glossitem>(.*?)<\/glossitem>/s';
        preg_match_all($pattern, $gloss_data, $matches, PREG_PATTERN_ORDER);
//now that we have them all, walk through each one and grab the term and definition
        foreach ($matches[1] as $match_item) {
//grab the term
            $pattern = '/<term>(.*?)<\/term>/';
            preg_match($pattern, $match_item, $matches);
            $term = $matches[1];
//grab the defintion
            $pattern = '/<definition>(.*?)<\/definition>/';
            preg_match($pattern, $match_item, $matches);
            $def = $matches[1];
//update the glossary with the item
            ModUtil::apiFunc('Book', 'admin', 'createglossary', array('term' => $term, 'definition' => $def));
        }
        return new RedirectResponse(ModUtil::url('book', 'admin', 'view'));
    }

    /**
     * @Route("/dosearchreplace")
     * 
     * Set up for the search replace feature of the module. The function diplsays
     * a form to the user for entrance of a search string, replace string, chooses
     * a book or chapter, and then whether to search through figures.
     * 
     * @param Request $request
     * @return type
     */
    public function dosearchreplaceAction(Request $request) {
        return $this->redirect($this->generateUrl('paustianbookmodule_admin_edit'));
        //you have to have edit permission to do this
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError(ModUtil::url('Book', 'admin', 'view'));
        }

        $chap_menus = $this->_generate_chapter_menu($this->view);
        $this->view->assign('chapters', $chap_menus);

        return $this->view->fetch('book_admin_dosearchreplace1.tpl');
    }

    public function dosearchreplace2($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Book', 'admin', 'dosearchreplace2'));
        }

//grab the serach and replace information
        $bid = FormUtil::getPassedValue('bid', isset($args['bid']) ? $args['bid'] : null);
        $search_pat = FormUtil::getPassedValue('search_pat', isset($args['search_pat']) ? $args['search_pat'] : null);
        $replace_pat = FormUtil::getPassedValue('replace_pat', isset($args['replace_pat']) ? $args['replace_pat'] : null);
        $preview = FormUtil::getPassedValue('preview', isset($args['preview']) ? $args['preview'] : null);
        $chap_to_get = 'chapter_' . $bid;
        $cid = FormUtil::getPassedValue($chap_to_get, isset($args[$chap_to_get]) ? $args[$chap_to_get] : null);
        $preview_text = "";
        if ($cid == 0) {
//do the whole book
            $preview_text = ModUtil::apiFunc('Book', 'admin', 'dosearchreplacebook', array('bid' => $bid,
                        'search_pat' => $search_pat,
                        'replace_pat' => $replace_pat,
                        'preview' => $preview === 'on'));
        } else {
            $preview_text = ModUtil::apiFunc('Book', 'admin', 'dosearchreplacechap', array('bid' => $bid,
                        'cid' => $cid,
                        'search_pat' => $search_pat,
                        'replace_pat' => $replace_pat,
                        'preview' => $preview === 'on'));
        }

        if ($preview === 'on') {
            $this->view->assign('preview_text', $preview_text);
            $this->view->assign('search_pat', $search_pat);
            $this->view->assign('replace_pat', $replace_pat);
            $this->view->assign('cid', $cid);
            $chap_menus = $this->_generate_chapter_menu($this->view);
            $this->view->assign('chapters', $chap_menus);
            return $this->view->fetch('book_admin_dosearchreplace1.tpl');
        }

        return new RedirectResponse(ModUtil::url('book', 'admin', 'dosearchreplace1'));
    }

}

?>