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
use Paustian\BookModule\Form\Book;
use Paustian\BookModule\Form\Chapter;
use Paustian\BookModule\Form\Article;
use Paustian\BookModule\Form\Figure;
use Paustian\BookModule\Form\Glossary;
use Paustian\BookModule\Form\ImportGloss;
use Paustian\BookModule\Form\ImportChapter;
use Paustian\BookModule\Form\SearchReplace;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController {

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
            throw new AccessDeniedException(__('You do not have pemission to access the Book admin interface.'));
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
        $repo = $this->getDoctrine()->getManager()->getRepository('PaustianBookModule:BookChaptersEntity');
        $chapters = $repo->getChapters($book->getBid());
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
        $doMerge = false;
        if (null === $chapter) {
            if (!SecurityUtil::checkPermission($this->name . '::', "::", ACCESS_ADD)) {
                throw new AccessDeniedException(__('You do not have permission to edit chapters.'));
            }
            $chapter = new BookChaptersEntity();
        } else {
            if (!SecurityUtil::checkPermission($this->name . '::Chapter', $chapter->getBid() . "::" . $chapter->getCid(), ACCESS_EDIT)) {
                throw new AccessDeniedException(__('You do not have permission to edit this chapters.'));
            }
            $doMerge = true;
        }
        $repo = $this->getDoctrine()->getManager()->getRepository('PaustianBookModule:BookEntity');
        $items = $repo->getBooks();
        if ($items === null) {
            //There are no books
            $this->addFlash('status', __('There are no books. Create a book first.'));
            return $this->redirect($this->generateUrl('paustianbookmodule_admin_edit'));
        }

        $form = $this->createForm(new Chapter(), $chapter);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
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
        $doMerge = false;
        if (null === $article) {
            if (!SecurityUtil::checkPermission($this->name . '::', "::", ACCESS_ADD)) {
                throw new AccessDeniedException(__('You do not have permission to create articles'));
            }
            $article = new BookArticlesEntity();
        } else {
            if (!SecurityUtil::checkPermission($this->name . '::Chapter', $article->getBid() . "::" . $article->getCid(), ACCESS_EDIT)) {
                throw new AccessDeniedException(__('You do not have permission to edit articles'));
            }
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
        $doMerge = false;
        if (null === $figure) {
            $figure = new BookFiguresEntity();
            if (!SecurityUtil::checkPermission($this->name . '::', ".*::", ACCESS_ADD)) {
                throw new AccessDeniedException(_('YOu do not have permission to create figures.'));
            }
        } else {
            $doMerge = true;
            if (!SecurityUtil::checkPermission($this->name . '::', $figure->getBid() . "::", ACCESS_EDIT)) {
                throw new AccessDeniedException(__('You do not have permission to edit figures.'));
            }
        }

        $form = $this->createForm(new Figure(), $figure);

        $repo = $this->getDoctrine()->getManager()->getRepository('PaustianBookModule:BookEntity');
        $books = $repo->getBooks();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $bid = $request->get('book');
            $figure->setBid($bid);

            if ($doMerge) {
                $em->merge($figure);
            } else {
                $em->persist($figure);
            }
            $em->flush();

            $this->addFlash('status', 'Figure Saved');
            return $this->redirect($this->generateUrl('paustianbookmodule_admin_editfigure'));
        }

        return $this->render('PaustianBookModule:Admin:book_admin_editfigure.html.twig', [
                    'form' => $form->createView(),
                    'books' => $books,
                    'figure' => $figure]);
    }

    /**
     * @Route("/editglossary/{gloss}")
     * 
     * @param Request $request
     * @param BookGlossEntity $gloss
     * @return type
     */
    public function editglossaryAction(Request $request, BookGlossEntity $gloss = null) {
        if (!SecurityUtil::checkPermission($this->name . '::', '.*::', ACCESS_ADD)) {
            throw new AccessDeniedException(__('You do not have permission to create glossary items.'));
        }
        $doMerge = false;
        if (null === $gloss) {
            $gloss = new BookGlossEntity();
        } else {
            $doMerge = true;
        }
        $form = $this->createForm(new Glossary(), $gloss);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($doMerge) {
                $em->merge($gloss);
            } else {
                $em->persist($gloss);
            }
            $em->flush();

            $this->addFlash('status', 'Glossary Item Saved');
            return $this->redirect($this->generateUrl('paustianbookmodule_admin_editglossary'));
        }

        return $this->render('PaustianBookModule:Admin:book_admin_editglossary.html.twig', [
                    'form' => $form->createView()]);
    }

    /**
     * @Route("/modify")
     * @param Request $request
     * @return type
     */
    public function modifyAction(Request $request) {
        $repo = $this->getDoctrine()->getManager()->getRepository('PaustianBookModule:BookEntity');
        $books = $repo->getBooks();

        return $this->render('PaustianBookModule:Admin:book_admin_modifybook.html.twig', ['books' => $books]);
    }

    private function _getChaptersAndBooks(&$chapters, &$books) {
        $chapters = $this->getDoctrine()->getManager()->getRepository('PaustianBookModule:BookChaptersEntity')->getChapters();
        $em = $this->getDoctrine()->getManager();
        $bookNames = array();
        foreach ($chapters as $chapter) {
            if ($chapter->getBid() === 0) {
                $bookNames[] = __('Unassigned');
            } else {
                $repo = $this->getDoctrine()->getManager()->getRepository('PaustianBookModule:BookEntity');
                $book = $repo->getBooks($chapter->getBid());
                //since we are getting one book, it is the first in the array.
                $bookNames[] = $book[0]->getName();
            }
        }
        $books = $bookNames;
    }

    /**
     * @Route("/modifychapter")
     * @param Request $request
     * @return type
     */
    public function modifychapterAction(Request $request) {

        $chapters = array();
        $books = array();
        $this->_getChaptersAndBooks($chapters, $books);
        return $this->render('PaustianBookModule:Admin:book_admin_modifychapter.html.twig', ['chapters' => $chapters,
                    'books' => $books]);
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
        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookEntity');
        $books = $repo->buildtoc();

        return $this->render('PaustianBookModule:Admin:book_admin_modifyarticle.html.twig', ['books' => $books]);
    }

    /**
     * @Route("/modifyfigure")
     * @param Request $request
     * @return type
     */
    public function modifyfigureAction(Request $request) {
        //get the list of books
        $repo = $this->getDoctrine()->getManager()->getRepository('PaustianBookModule:BookEntity');
        $books = $repo->getBooks();
        $renderBooks = array();
        $bookArray = array();
        //build up each set of figures separated
        //upon their chapter and their book.    
        $repoFig = $this->getDoctrine()->getManager()->getRepository('PaustianBookModule:BookFiguresEntity');
        foreach ($books as $book) {
            $bookArray['bid'] = $book->getBid();
            $bookArray['figures'] = $repoFig->getFigures($bookArray['bid']);
            if (count($bookArray['figures']) == 0) {
                continue;
            }
            $bookArray['bookName'] = $book->getName();
            $renderBooks[] = $bookArray;
            $bookArray = array();
        }

        return $this->render('PaustianBookModule:Admin:book_admin_modifyfigure.html.twig', ['books' => $renderBooks]);
    }

    /**
     * @Route("/modifyglossary/{letter}", defaults={"letter"="A"})
     * @return type
     */
    public function modifyglossaryAction($letter) {
        $repo = $this->getDoctrine()->getManager()->getRepository('PaustianBookModule:BookGlossEntity');
        $terms = $repo->getGloss($letter, ['col' => 'u.term', 'direction' => 'ASC'], null, ['u.term', 'u.gid']);
        return $this->render('PaustianBookModule:Admin:book_admin_modifyglossary.html.twig', ['terms' => $terms]);
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
     * @Route("/export/{chapter}")
     * 
     * @param Request $request
     * @param BookChaptersEntity $chapter - the chapter to export
     * @return type
     */
    public function exportAction(Request $request, BookChaptersEntity $chapter = null) {
        $response = $this->redirect($this->generateUrl('paustianbookmodule_admin_modifychapter'));
        if ($chapter == null) {
            //you want the edit interface, which has a delete option.
            return $response;
        }
        if (!SecurityUtil::checkPermission('::Chapter', "::" . $chapter->getCid(), ACCESS_DELETE)) {
            throw new AccessDeniedException($this->__("You do not have permission to export that chapter."));
        }
        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookArticlesEntity');
        $articles = $repo->getArticles($chapter->getCid(), true, true);
        //The rest of this can be done in the template.
        return $this->render('PaustianBookModule:Admin:book_admin_export.html.twig', ['chapter' => $chapter,
                    'articles' => $articles]);
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
        $repo = $this->getDoctrine()->getManager()->getRepository('PaustianBookModule:BookArticlesEntity');
        $articles = $repo->getArticles($chapter->getCid(), false, false);
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
    public function deletefigureAction(Request $request, BookFiguresEntity $figure = null) {
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
    public function deleteglossaryAction(Request $request, BookGlossEntity $gloss = null) {
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
        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookEntity');
        $books = $repo->buildtoc(0, $chapterids);

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
            if ($order == '') {
                continue;
            }
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
     * @Route("/import")
     *
     * Import a chapter into the textbook. This should take the exported text
     * and reprocess it. One problem I may need to solve is munged text.
     * I will have to do some serious checking for missing params, and if not
     * there, unwind the whole process.
     */
    public function importAction(Request $request) {
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException($this->__("You do not have permission to import text to books."));
        }
        $form = $this->createForm(new ImportChapter());

        $form->handleRequest($request);

        if ($form->isValid()) {
            $xmlQuestionText = $form->get('importText')->getData();
            //take the xml that is imported, and parse it into an array
            //That array should have filled out a new question entity which it shoudl return


            $this->getDoctrine()->getRepository('PaustianBookModule:BookArticlesEntity')->parseImportedChapterXML($xmlQuestionText);
            $this->addFlash('status', __("Chapter imported."));

            $response = $this->redirect($this->generateUrl('paustianbookmodule_admin_import'));
            return $response;
        }

        return $this->render('PaustianBookModule:Admin:book_admin_import.html.twig', array(
                    'form' => $form->createView(),
        ));
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

    /**
     * @Route("/verifyurls/{chapter}")
     * 
     * verifyurls
     *
     * Given a book id or chapter id, work through the articles in the book and find each
     * url. Then check them all. When the checking is done, a report lists all the bad urls
     * and their article location. Note, this does not list internal links, since those return
     * without error due to the way zikula is set up.
     */
    public function verifyurlsAction(Request $request, BookChaptersEntity $chapter = null) {
        //if there is not chapter, then redirect to the modify chapter screen.
        $response = $this->redirect($this->generateUrl('paustianbookmodule_admin_modifychapter'));
        $cid = null;
        $bid = null;
        if (null === $chapter) {
            //you want the edit interface, which has a delete option.
            return $response;
        } else {
            $cid = $chapter->getCid();
            $bid = $chapter->getBid();
            if (!SecurityUtil::checkPermission('Book::Chatper', "$bid::$cid", ACCESS_EDIT)) {
                throw new AccessDeniedException($this->__("You do not have permission to verify urls in chapters."));
            }
        }

        $url_table = array();
        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookArticlesEntity');
        //grab the articles and their content
        $articles = $repo->getArticles($cid, false, true);

        foreach ($articles as $article) {
            // Security check
            if (SecurityUtil::checkPermission('Book::', "$bid::$cid", ACCESS_EDIT)) {
                $this->buildtable($article->getContents(), $url_table, $chapter->getNumber(), $article->getNumber());
            }
        }
        
        return $this->render('PaustianBookModule:Admin:book_admin_verifyurls.html.twig', 
                ['urltable' => $url_table]);
    }

    function buildtable($content, &$url_table, $chap_no, $article_no) {
        $matches = array();
        $url_row = array();
        $new_urls = array();
        $pattern = "/<a\s+href=\"([^\" ]*?)\"[^>]*?>/siU";
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $url_item) {
            $url_row['url'] = $url_item[1];
            $url_row['chap_no'] = $chap_no;
            $url_row['article_no'] = $article_no;
            $new_urls[] = $url_row;
        }
        $new_urls = $this->checkurls($new_urls);
        $url_table = array_merge($url_table, $new_urls);
    }

    public function checkurls($urls) {
        //the url to the current server
        $baseurl = ModUtil::getBaseDir();
        $i = 0;
        foreach ($urls as $items) {
            //check to see if it is a valid url
            if (!$this->_is_url($items['url'])) {
                if (preg_match("/^\\//", $items['url'])) {
                    //root directory. Append the host and stop
                    //remove the first /
                    $items['url'] = Request::getScheme() . "://" . Request::getHost() . $items['url'];
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
                    $parts[0] = str_replace('amp;', '', $parts[0]);
                    $arr_query[$parts[0]] = $parts[1];
                }
                $modname = $arr_query['module'];
                if (isset($arr_query['type'])) {
                    $type = $arr_query['type'];
                } else {
                    $type = 'user';
                }
                if (isset($arr_query['func'])) {
                    $func = $arr_query['func'];
                } else {
                    $func = 'main';
                }
                //check to see if we can actually call this function
                if (ModUtil::getCallable($modname, $type, $func)) {
                    $urls[$i]['valid'] = 'Yes';
                } else {
                    $urls[$i]['valid'] = 'No';
                }
            } else {
                $urls[$i]['valid'] = $this->_check_http_link($items);
            }
            $i++;
        }

        return $urls;
    }

    private function _is_url($url) {
        if (!preg_match('/^http\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+/i', $url)) {
            return false;
        } else {
            return true;
        }
    }

    private function _check_http_link($inItem) {
        if ($this->_is_valid_url($inItem['url'])) {
            return 'Yes';
        } else {
            return 'No';
        }
    }

    private function _is_valid_url($url) {
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
            $headers = get_headers("$url[scheme]://$url[host]:$url[port]$path");
            return (bool) preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers[0]);
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
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException($this->__("You do not have permission to edit glossary items."));
        }

        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookGlossEntity');
        $where = ['cond' => 'u.definition = ?1', 'paramkey' => 1, 'paramval' => ''];
        $glossaryItems = $repo->getGloss('', null, $where);

        return $this->render('PaustianBookModule:Admin:book_admin_studentdefgloss.html.twig', ['glossaryItems' => $glossaryItems]);
    }

    /**
     * @Route("/importglossary")
     * @param Request $request
     * @return type
     */
    public function importglossaryAction(Request $request) {
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException($this->__("You do not have permission to import glossary items."));
        }
        $form = $this->createForm(new ImportGloss());

        $form->handleRequest($request);

        if ($form->isValid()) {
            $xmlQuestionText = $form->get('importText')->getData();
            //take the xml that is imported, and parse it into an array
            //That array should have filled out a new question entity which it shoudl return
            $defQuestions = $this->getDoctrine()->getRepository('PaustianBookModule:BookGlossEntity')->parseImportedGlossXML($xmlQuestionText);
            if (count($defQuestions)) {
                $terms = '';
                foreach ($defQuestions as $question) {
                    $terms .= $question . ', ';
                }
                $this->addFlash('status', __("Glossary items imported except for " . $terms . "which were alraedy defined."));
            } else {
                $this->addFlash('status', __("Glossary items imported."));
            }
            $response = $this->redirect($this->generateUrl('paustianbookmodule_admin_importglossary'));
            return $response;
        }

        return $this->render('PaustianBookModule:Admin:book_admin_importgloss.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/searchreplace/{chapter}")
     * 
     * Set up for the search replace feature of the module. The function diplsays
     * a form to the user for entrance of a search string, replace string, chooses
     * a chapter, and then whether to search through figures.
     * 
     * @param Request $request
     * @param BookChaptersEntity $chapter
     * @return type
     */
    public function searchreplaceAction(Request $request, BookChaptersEntity $chapter = null) {
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException($this->__("You do not have permission to perform a search and replace."));
        }
        $response = $this->redirect($this->generateUrl('paustianbookmodule_admin_modifychapter'));
        if (null == $chapter) {
            return $response;
        }
        $form = $this->createForm(new SearchReplace());
        $preview = "";
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookArticlesEntity');
            $valid = (@preg_match($data['searchText'], '') !== FALSE);
            if ($valid) {
                $previewText = $repo->searchAndReplaceText($data['searchText'], $data['replaceText'], $data['preview'], $chapter->getCid());
                if (!$data['preview']) {
                    $this->addFlash('status', __("Search and Replace Finished"));
                    return $response;
                }
            } else {
                $this->addFlash('error', __("Your search string was invliad"));
            }
            //if we did a preview or there was an error in the serach tring, then just fall through and show it.
        }

        return $this->render('PaustianBookModule:Admin:book_admin_searchreplace.html.twig', ['form' => $form->createView(), 'preview' => $previewText, 'chapter' => $chapter]);
    }
}