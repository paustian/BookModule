<?php

// ----------------------------------------------------------------------
// Original Author of file: Timothy Paustian
// Purpose of file:  Book administration display functions
// ----------------------------------------------------------------------

namespace Paustian\BookModule\Controller;

use Zikula\Bundle\HookBundle\FormAwareHook\FormAwareHook;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController {

    private $theRequest;

    /**
     * @Route("")
     * @Theme("admin")
     * @param request - the incoming request.
     * @Template("@PaustianBookModule/Admin/book_admin_menu.html.twig'")
     * @return Response|array
     * The main entry point
     *
     * @return Response The rendered output consisting mainly of the admin menu
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function indexAction(Request $request) {
        //security check
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException($this->trans('You do not have pemission to access the Book admin interface.'));
        }
        // Return a page of menu items.
        return [];
    }

    /**
     *
     * @Route("/edit/{book}")
     * @Theme("admin")
     * @Template("@PaustianBookModule/Admin/book_admin_editbook.html.twig")
     * @return Response|array
     * Create a new book. This presents the form for giving a title to the book
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function editAction(Request $request, BookEntity $book = null) {
        $doMerge = false;
        if (null === $book) {
            if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
                throw new AccessDeniedException($this->trans("You do not have permission to edit books."));
            }
            $book = new BookEntity();
        } else {
            if (!$this->hasPermission($this->name . '::', $book->getBid() . '::', ACCESS_ADD)) {
                throw new AccessDeniedException($this->trans("You do not have permission to edit this book."));
            }
            $doMerge = true;
        }

        $form = $this->createForm(Book::class, $book);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if ($doMerge) {
                $em->merge($book);
            } else {
                $em->persist($book);
            }
            $em->flush();

            $this->addFlash('status', $this->trans('Book Saved'));
            return $this->redirect($this->generateUrl('paustianbookmodule_admin_edit'));
        }

        return[
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/delete/{book}")
     * @Theme("admin")
     * @param Request $request
     * @param BookEntity $book
     * @return Response
     */
    public function deleteAction(Request $request, BookEntity $book = null) {
        if (!$this->hasPermission('book::', $book->getBid() . "::", ACCESS_DELETE)) {
            throw new AccessDeniedException($this->trans("You do not have permission to delete that book."));
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
        $this->addFlash('status', $this->trans('Book Deleted.'));
        return $response;
    }

    /**
     *
     * @Route("/editchapter/{chapter}")
     * @Theme("admin")
     * @param Request $request
     * @param \Paustian\BookModule\Controller\BookChaptersEntity $chapter
     * @return RedirectRespsonse | Response | AccessDeniedException
     * @throws AccessDeniedException
     */
    public function editchapterAction(Request $request,
                                      BookChaptersEntity $chapter = null) {
        $doMerge = false;
        if (null === $chapter) {
            if (!$this->hasPermission($this->name . '::', "::", ACCESS_ADD)) {
                throw new AccessDeniedException($this->trans('You do not have permission to edit chapters.'));
            }
            $chapter = new BookChaptersEntity();
        } else {
            if (!$this->hasPermission($this->name . '::Chapter', $chapter->getBid() . "::" . $chapter->getCid(), ACCESS_EDIT)) {
                throw new AccessDeniedException($this->trans('You do not have permission to edit this chapters.'));
            }
            $doMerge = true;
        }
        $repo = $this->getDoctrine()->getManager()->getRepository('PaustianBookModule:BookEntity');
        $items = $repo->getBooks();
        if ($items === null) {
            //There are no books
            $this->addFlash('status', $this->trans('There are no books. Create a book first.'));
            return $this->redirect($this->generateUrl('paustianbookmodule_admin_edit'));
        }

        $form = $this->createForm(Chapter::class, $chapter);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $bid = (int)$request->get('book');
            $chapter->setBid($bid);
            $route = 'paustianbookmodule_admin_editchapter';
            $flashText = $this->trans('Chapter ' . $chapter->getName() . ' Saved');
            if ($doMerge) {
                $route = 'paustianbookmodule_admin_modifychapter';
                $flashText = $this->trans('Chapter ' . $chapter->getName() . ' Updated');
                $em->merge($chapter);
            } else {
                $em->persist($chapter);
            }
            $em->flush();

            $this->addFlash('status', $flashText);
            return $this->redirect($this->generateUrl($route));
        }

        return $this->render('@PaustianBookModule/Admin/book_admin_editchapter.html.twig', array(
                    'form' => $form->createView(),
                    'books' => $items,
                    'chapter' => $chapter
        ));
    }

    /**
     * @Route("/editarticle/{article}")
     * @Theme("admin")
     * edit an article.
     * @param Request $request
     * @param BookArticlesEntity|null $article
     * @return RedirectResponse|Response
     */
    public function editarticleAction(Request $request,
                                      BookArticlesEntity $article = null,
                                      HookDispatcherInterface $hookDispatcher) {
        $doMerge = false;
        if (null === $article) {
            if (!$this->hasPermission($this->name . '::', "::", ACCESS_ADD)) {
                throw new AccessDeniedException($this->trans('You do not have permission to create articles'));
            }
            $article = new BookArticlesEntity();
        } else {
            if (!$this->hasPermission($this->name . '::Chapter', $article->getBid() . "::" . $article->getCid(), ACCESS_EDIT)) {
                throw new AccessDeniedException($this->trans('You do not have permission to edit articles'));
            }
            $doMerge = true;
        }

        $form = $this->createForm(Article::class, $article, ['locale' => $request->getLocale()]);
        $formHook = new FormAwareHook($form);
        $hookDispatcher->dispatch('book.form_aware_hook.article.edit', $formHook);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //upon creation, articles are not attached to books
            //you attach them later in a drag and drop interface
            $em = $this->getDoctrine()->getManager();
            $route = 'paustianbookmodule_admin_editarticle';
            $flashText = $this->trans('Article ' . $article->getTitle() .  ' Saved');
            if ($doMerge) {
                $em->merge($article);
                $route = 'paustianbookmodule_admin_modifyarticle';
                $flashText = $this->trans('Article ' . $article->getTitle() .  ' Updated');
            } else {
                //This is a new article so Book and Chapter not set.
                $article->setBid(0);
                $article->setCid(0);
                $em->persist($article);
            }
            $em->flush();
            $this->addFlash('status', $flashText);
            return $this->redirect($this->generateUrl($route));
        }

        return $this->render('@PaustianBookModule/Admin/book_admin_editarticle.html.twig', [
                    'form' => $form->createView(),
                    'hook_templates' => $formHook->getTemplates()]);
    }

    /**
     * @Route("/editfigure/{figure}")
     * @Theme("admin")
     * @param Request $request
     * @param BookFiguresEntity|null $figure
     * @return RedirectResponse|Response
     */
    public function editfigureAction(Request $request,
                                     BookFiguresEntity $figure = null,
                                     HookDispatcherInterface $hookDispatcher) {
        $doMerge = false;
        if (null === $figure) {
            $figure = new BookFiguresEntity();
            if (!$this->hasPermission($this->name . '::', ".*::", ACCESS_ADD)) {
                throw new AccessDeniedException($this->trans('You do not have permission to create figures.'));
            }
        } else {
            $doMerge = true;
            if (!$this->hasPermission($this->name . '::', $figure->getBid() . "::", ACCESS_EDIT)) {
                throw new AccessDeniedException($this->trans('You do not have permission to edit figures.'));
            }
        }

        $form = $this->createForm(Figure::class, $figure);
        $formHook = new FormAwareHook($form);
        $hookDispatcher->dispatch('book.form_aware_hook.article.edit', $formHook);
        $repo = $this->getDoctrine()->getManager()->getRepository('PaustianBookModule:BookEntity');
        $books = $repo->getBooks();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $bid = (int)$request->get('book');
            $figure->setBid($bid);
            $route ='paustianbookmodule_admin_editfigure';
            $flashText = $this->trans('Figure Saved');
            if ($doMerge) {
                $route = 'paustianbookmodule_admin_modifyfigure';
                $flashText = $this->trans('Figure Updated');
                $em->merge($figure);
            } else {
                $em->persist($figure);
            }
            $em->flush();
            $this->addFlash('status', $flashText);
            return $this->redirect($this->generateUrl($route));
        }

        return $this->render('@PaustianBookModule/Admin/book_admin_editfigure.html.twig', [
                    'form' => $form->createView(),
                    'books' => $books,
                    'figure' => $figure,
            'hook_templates' => $formHook->getTemplates()]);
    }

    /**
     * @Route("/editglossary/{gloss}")
     * @Theme("admin")
     * @param Request $request
     * @param BookGlossEntity $gloss
     * @return Response
     * @return RedirectResponse
     */
    public function editglossaryAction(Request $request,
                                       BookGlossEntity $gloss = null,
                                       HookDispatcherInterface $hookDispatcher) {
        if (!$this->hasPermission($this->name . '::', '.*::', ACCESS_ADD)) {
            throw new AccessDeniedException($this->trans('You do not have permission to create glossary items.'));
        }
        $doMerge = false;
        if (null === $gloss) {
            $gloss = new BookGlossEntity();
        } else {
            $doMerge = true;
        }
        $form = $this->createForm(Glossary::class, $gloss);
        $formHook = new FormAwareHook($form);
        $hookDispatcher->dispatch('book.form_aware_hook.article.edit', $formHook);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $route = 'paustianbookmodule_admin_editglossary';
            $flashText = $this->trans('Glossary Term ' . $gloss->getTerm() . ' Saved');
            if ($doMerge) {
                $route = 'paustianbookmodule_admin_modifyglossary';
                $flashText = $this->trans('Glossary Term ' . $gloss->getTerm() . ' Updated');
                $em->merge($gloss);
            } else {
                $em->persist($gloss);
            }
            $em->flush();

            $this->addFlash('status', $flashText);
            return $this->redirect($this->generateUrl($route));
        }

        return $this->render('@PaustianBookModule/Admin/book_admin_editglossary.html.twig', [
                    'form' => $form->createView(),
            'hook_templates' => $formHook->getTemplates()]);
    }

    /**
     * @Route("/modify")
     * @Theme("admin")
     * @param Request $request
     * @return Response
     */
    public function modifyAction(Request $request) {
        $repo = $this->getDoctrine()->getManager()->getRepository('PaustianBookModule:BookEntity');
        $books = $repo->getBooks();

        return $this->render('@PaustianBookModule/Admin/book_admin_modifybook.html.twig', ['books' => $books]);
    }

    private function _getChaptersAndBooks(&$chapters, &$books) {
        $chapters = $this->getDoctrine()->getManager()->getRepository('PaustianBookModule:BookChaptersEntity')->getChapters();
        $em = $this->getDoctrine()->getManager();
        $bookNames = array();
        foreach ($chapters as $chapter) {
            if ($chapter->getBid() === 0) {
                $bookNames[] = $this->trans('Unassigned');
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
     * @Theme("admin")
     * @param Request $request
     * @return Response
     */
    public function modifychapterAction(Request $request) {

        $chapters = array();
        $books = array();
        $this->_getChaptersAndBooks($chapters, $books);
        return $this->render('@PaustianBookModule/Admin/book_admin_modifychapter.html.twig', ['chapters' => $chapters,
                    'books' => $books]);
    }

    /**
     * @Route("/modifyarticle")
     * @Theme("admin")
     *
     * Create an interface for picking the article you want to edit
     * @param Request $request
     * @return Response
     */
    public function modifyarticleAction(Request $request, BookArticlesEntity $article = null) {
        //build an organization of the book
        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookEntity');
        $books = $repo->buildtoc();

        return $this->render('@PaustianBookModule/Admin/book_admin_modifyarticle.html.twig', ['books' => $books]);
    }

    /**
     * @Route("/modifyfigure")
     * @Theme("admin")
     * @param Request $request
     * @return Response
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

        return $this->render('@PaustianBookModule/Admin/book_admin_modifyfigure.html.twig', ['books' => $renderBooks]);
    }

    /**
     * @Route("/modifyglossary/{letter}", defaults={"letter"="A"})
     * @Theme("admin")
     * @return Response
     */
    public function modifyglossaryAction(string $letter) {
        $repo = $this->getDoctrine()->getManager()->getRepository('PaustianBookModule:BookGlossEntity');
        $terms = $repo->getGloss($letter, ['col' => 'u.term', 'direction' => 'ASC'], null, ['u.term', 'u.gid']);
        return $this->render('@PaustianBookModule/Admin/book_admin_modifyglossary.html.twig', ['terms' => $terms]);
    }

    private function _generate_chapter_menu() {
        //get the complete list of books
        $repo = $this->getDoctrine()->getManager()-getRepository('@PaustianBookModule\BookEntity');
        $books = $repo->getBooks();
        if ($books == false) {
            //if we dont' have a book, then you
            //cannot have chapters
            $this->addFlash('error', $this->trans('You have to create a book before you can create a chapter or an article'));
            return null;
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
            $repo = $this->getDoctrine()->getManager()->getRepository('PaustianBookModule:BookChaptersEntity');
            $chap_info = $repo->getChapters($bid);
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
            $this->addFlash('error', $this->trans('There are no chapters.'));
            return null;
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
     * @Route("/export/{chapter}/{inlinefig}")
     * @Theme("admin")
     * @param Request $request
     * @param BookChaptersEntity $chapter - the chapter to export
     * @return Response
     */
    public function exportAction(Request $request, BookChaptersEntity $chapter = null, $inlinefig=true) {
        $response = $this->redirect($this->generateUrl('paustianbookmodule_admin_modifychapter'));
        if ($chapter == null) {
            //you want the edit interface, which has a delete option.
            return $response;
        }
        if (!$this->hasPermission($this->name . '::Chapter', "::" . $chapter->getCid(), ACCESS_DELETE)) {
            throw new AccessDeniedException($this->trans("You do not have permission to export that chapter."));
        }
        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookArticlesEntity');
        $articles = $repo->getArticles($chapter->getCid(), true, true);
        $response = "";
        if($inlinefig){
            $return_text = $this->render('@PaustianBookModule/User/book_user_displayarticlesinchapter.html.twig', ['chapter' => $chapter, 'articles' => $articles])->getContent();
            $return_text = $repo->addfigures($return_text, $this);
            $response = $this->render('@PaustianBookModule/Admin/book_admin_export2.html.twig', ['chapter' => $chapter, 'text' => $return_text]);
        } else {
            $response = $this->render('@PaustianBookModule/Admin/book_admin_export.html.twig', ['chapter' => $chapter,
                'articles' => $articles]);
        }
        return $response;
    }

    /**
     * @Route("deletechapter/{chapter}")
     * @Theme("admin")
     * @param Request $request
     * @param BookChaptersEntity $chapter
     * @return Response
     * @throws AccessDeniedException
     */
    public function deletechapterAction(Request $request, BookChaptersEntity $chapter = null) {
        $response = $this->redirect($this->generateUrl('paustianbookmodule_admin_modifychapter'));
        if ($chapter == null) {
            //you want the edit interface, which has a delete option.
            return $response;
        }

        if (!$this->hasPermission($this->name . '::Chapter', "::" . $chapter->getCid(), ACCESS_DELETE)) {
            throw new AccessDeniedException($this->trans("You do not have permission to delete that chapter."));
        }


        //I need to walk the articles and remove any reference to this book
        //Set the chapters to 0
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('PaustianBookModule:BookArticlesEntity');
        $articles = $repo->getArticles($chapter->getCid(), false, false);
        foreach ($articles as $article) {
            $artClass = $em->find('PaustianBookModule:BookArticlesEntity', $article['aid']);
            $artClass->setCid(0);
        }

        $em->remove($chapter);
        $em->flush();
        $this->addFlash('status', $this->trans('Chapter Deleted.'));
        return $response;
    }

    /**
     * @Route("deletearticle/{article}")
     * @Theme("admin")
     * @param Request $request
     * @param BookArticlesEntity $article
     * @return RedirectResponse
     */
    public function deletearticleAction(Request $request,
                                        BookArticlesEntity $article = null,
                                        HookDispatcherInterface $hookDispatcher) {

        $response = $this->redirect($this->generateUrl('paustianbookmodule_admin_modifyarticle'));
        if (null === $article) {
            //you want the edit interface, which has a delete option.
            return $response;
        } else {
            if (!$this->hasPermission($this->name . '::Chapter', $article->getBid() . "::" . $article->getCid(), ACCESS_DELETE)) {
                throw new AccessDeniedException($this->trans("You do not have permission to delete this article."));
            }
        }

        $em = $this->getDoctrine()->getManager();


        $em->remove($article);
        $em->flush();
        $this->addFlash('status', $this->trans('Article Deleted.'));
        //Let any providers hooked to this article that it was deleted.
        $hookDispatcher->dispatch(\Paustian\BookModule\HookSubscriber\ArticleUiHookSubscriber::ARTICLE_DELETE_PROCESS, new ProcessHook($article->getAid()));

        return $response;
    }

    /**
     * @Route("/deletefigure/{figure}")
     * @Theme("admin")
     * @param Request $request
     * @param BookFiguresEntity $figure
     * @return RedirectResponse
     * @throws AccessDeniedException
     */
    public function deletefigureAction(Request $request, BookFiguresEntity $figure = null) {
        $response = $this->redirect($this->generateUrl('paustianbookmodule_admin_modifyfigure'));
        if ($figure == null) {
            if (!$this->hasPermission($this->name . '::', "::", ACCESS_DELETE)) {
                throw new AccessDeniedException($this->trans("You do not have permission to delete figures."));
            }
            //you want the edit interface, which has a delete option.
            return $response;
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($figure);
        $em->flush();
        $this->addFlash('status', $this->trans('Figure deleted.'));
        return $response;
    }

    /**
     * @Route("deleteglossary/{gloss}")
     * @Theme("admin")
     * @param Request $request
     * @param BookGlossEntity $gloss
     * @return Response
     * @throws AccessDeniedException
     */
    public function deleteglossaryAction(Request $request, BookGlossEntity $gloss = null) {
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_DELETE)) {
            throw new AccessDeniedException($this->trans("You do not have permission to delete that glossary item."));
        }
        if ($gloss == null) {
            //you want the edit interface, which has a delete option.
            return $this->redirect($this->generateUrl('paustianbookmodule_admin_modifyglossary'));
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($gloss);
        $em->flush();
        $this->addFlash('status', $this->trans('Glossary item deleted.'));
        $firstLetter = strtoupper(substr($gloss->getTerm(), 0, 1));
        return $this->redirect($this->generateUrl('paustianbookmodule_admin_modifyglossary', ['letter' => $firstLetter]));
    }

    /**
     * This is a standard function to update the configuration parameters of the
     * module given the information passed back by the modification form
     */
    public function updateconfig() {
        return true;
    }

    /**
     * @Route("/arrangearticlesAction")
     * @Theme("admin")
     * @param Request $request
     * @return Response
     */
    public function arrangearticlesAction(Request $request)  : Response {
        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookEntity');
        $chapterids=[];
        $books = $repo->buildtoc(0, $chapterids);

        return $this->render('@PaustianBookModule/Admin/book_admin_arrangearticles.html.twig', ['books' => $books,
                    'chapterids' => $chapterids]);
    }

    /**
     * @Route("/savearrangement")
     * @Theme("admin")
     * @param Request $request
     * @return Response
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
                $article->setCid((int)$chapterId);
                $article->setBid($bookId);
                if ($oldArticle != null) {
                    if ($bookId == 0) {
                        $article->setPrev(0);
                        $oldArticle->setNext(0);
                    } else {
                        $article->setPrev($oldArticle->getAid());
                        $oldArticle->setNext((int)$aid);
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
        $this->addFlash('status', $this->trans("Article arrangement saved."));
        return $this->redirect($this->generateUrl('paustianbookmodule_admin_arrangearticles'));
    }

    /**
     * @Route("/import")
     * @Theme("admin")
     * Import a chapter into the textbook. This should take the exported text
     * and reprocess it. One problem I may need to solve is munged text.
     * I will have to do some serious checking for missing params, and if not
     * there, unwind the whole process.
     */
    public function importAction(Request $request) {
        if (!$this->hasPermission($this->name. '::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException($this->trans("You do not have permission to import text to books."));
        }
        $form = $this->createForm(ImportChapter::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $xmlQuestionText = $form->get('importText')->getData();
            //take the xml that is imported, and parse it into an array
            //That array should have filled out a new question entity which it shoudl return


            $this->getDoctrine()->getRepository('PaustianBookModule:BookArticlesEntity')->parseImportedChapterXML($xmlQuestionText);
            $this->addFlash('status', $this->trans("Chapter imported."));

            $response = $this->redirect($this->generateUrl('paustianbookmodule_admin_import'));
            return $response;
        }

        return $this->render('@PaustianBookModule/Admin/book_admin_import.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /*public function modifyimagepaths($args) {
        //only admins can do this
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            return new ForbiddenResponse($this->trans('Access Denied'));
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
                $this->addFlash('status', $this->trans('The figure was updated.'));
            } else {
                $this->addFlash('error', $this->trans('Update of figure failed.'));
                return false;
            }
        }

        return new RedirectResponse(ModUtil::url('book', 'admin', 'dolistbookfigures'));
    }*/

    /**
     * @Route("/verifyurls/{chapter}")
     * @Theme("admin")
     * verifyurls
     *
     * Given a book id or chapter id, work through the articles in the book and find each
     * url. Then check them all. When the checking is done, a report lists all the bad urls
     * and their article location. Note, this does not list internal links, since those return
     * without error due to the way zikula is set up.
     */
    public function verifyurlsAction(Request $request, BookChaptersEntity $chapter = null) {
        $this->theRequest = $request;
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
            if (!$this->hasPermission($this->name . '::Chatper', "$bid::$cid", ACCESS_EDIT)) {
                throw new AccessDeniedException($this->trans("You do not have permission to verify urls in chapters."));
            }
        }

        $url_table = array();
        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookArticlesEntity');
        //grab the articles and their content
        $articles = $repo->getArticles($cid, false, true);

        foreach ($articles as $article) {
            // Security check
            if ($this->hasPermission($this->name . '::', "$bid::$cid", ACCESS_EDIT)) {
                $this->buildtable($article->getContents(), $url_table, $chapter->getNumber(), $article->getNumber());
            }
        }

        return $this->render('@PaustianBookModule/Admin/book_admin_verifyurls.html.twig',
                ['urltable' => $url_table]);
    }

    function buildtable(string $content, array &$url_table, int $chap_no, int $article_no) {
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

    /**
     * checkurls
     *
     * This function checks the urls in a chapter and sees if they are real links
     * The internal links use the router to see if they match. It fails if you use old style
     * ugly urls. This is by design to encourage changing the url style
     *
     * The external links check using
     * @param $urls
     * @return mixed
     */
    public function checkurls(array $urls) {
        //the url to the current server
        $baseurl = $GLOBALS['request']->getBaseUrl();
        $i = 0;
        foreach ($urls as $items) {
            //check to see if it is a valid url
            if ($this->_is_url($items['url'])) {
                $urls[$i]['valid'] = $this->_check_http_link($items);

            } else {
                //if not its a local link.
                //strip out the baseurl
                $items['url'] = str_replace($baseurl, "", $items['url']);
                try {
                    $result = $this->get('router')->match($items['url']);
                    //determine if the url is valid based upon whether it found an object
                    $urls[$i]['valid'] = 'Yes';
                } catch (\Exception $e) {
                    //we don't want this to blow up the script, just catch it
                    $urls[$i]['valid'] = 'No';
                }
            }
            $i++;
        }

        return $urls;
    }

    private function _is_url(string $url) {
        if (!preg_match('/^http[s]*\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+/i', $url)) {
            return false;
        } else {
            return true;
        }
    }

    private function _check_http_link(array $inItem) {
        if ($this->_is_valid_url($inItem['url'])) {
            return 'Yes';
        } else {
            return 'No';
        }
    }

    private function _is_valid_url(string $url) {
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
     * @Theme("admin")
     * @param Request $request
     * @return boolean
     * Students can request words to be defined. These will appear as words with empty definitions.
     * This routine will find all empty definitions in the glossary and then display them to the author.
     * The author can then define them.
     */
    public function checkstudentdefsAction(Request $request) {
        if (!$this->hasPermission($this->name. '::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException($this->trans("You do not have permission to edit glossary items."));
        }

        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookGlossEntity');
        $glossaryItems = $repo->getUndefinedTerms();
        return $this->render('@PaustianBookModule/Admin/book_admin_studentdefgloss.html.twig', ['glossaryItems' => $glossaryItems]);
    }

    /**
     * @Route("/importglossary")
     * @Theme("admin")
     * @param Request $request
     * @return Response
     */
    public function importglossaryAction(Request $request) {
        if (!$this->hasPermission($this->name. '::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException($this->trans("You do not have permission to import glossary items."));
        }
        $form = $this->createForm(ImportGloss::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $xmlQuestionText = $form->get('importText')->getData();
            //take the xml that is imported, and parse it into an array
            //That array should have filled out a new question entity which it shoudl return
            $defTerms = $this->getDoctrine()->getRepository('PaustianBookModule:BookGlossEntity')->parseImportedGlossXML($xmlQuestionText);

            $this->addFlash('status', $this->trans("Glossary import finished: $defTerms."));
            return $this->redirect($this->generateUrl('paustianbookmodule_admin_importglossary'));
        }

        return $this->render('@PaustianBookModule/Admin/book_admin_importgloss.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/searchreplace/{chapter}")
     * @Theme("admin")
     * Set up for the search replace feature of the module. The function diplsays
     * a form to the user for entrance of a search string, replace string, chooses
     * a chapter, and then whether to search through figures.
     *
     * @param Request $request
     * @param BookChaptersEntity $chapter
     * @return Response
     */
    public function searchreplaceAction(Request $request, BookChaptersEntity $chapter = null) {
        if (!$this->hasPermission($this->name. '::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException($this->trans("You do not have permission to perform a search and replace."));
        }
        $response = $this->redirect($this->generateUrl('paustianbookmodule_admin_modifychapter'));
        if (null == $chapter) {
            return $response;
        }
        $form = $this->createForm(SearchReplace::class);

        $form->handleRequest($request);
        $previewText="";
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookArticlesEntity');
            $valid = (@preg_match($data['searchText'], '') !== FALSE);
            if ($valid) {
                $count = 0;
                $previewText = $repo->searchAndReplaceText($data['searchText'], $data['replaceText'], $data['preview'], $chapter->getCid(), $count);
                if (!$data['preview']) {
                    $this->addFlash('status', $this->trans("Search and Replace Finished, $count replacements were made."));
                    return $response;
                } else {
                    $this->addFlash('status', $this->trans("Preview of Search and Replace Finished, $count replacements were made."));
                }
            } else {
                $this->addFlash('error', $this->trans("Your search string was invliad"));
            }
            //if we did a preview or there was an error in the serach string, then just fall through and show it.
        }

        return $this->render('@PaustianBookModule/Admin/book_admin_searchreplace.html.twig', ['form' => $form->createView(), 'preview' => $previewText, 'chapter' => $chapter]);
    }

    /**
     * @Route("addglossarytoarticle/{article}")
     * @Theme("admin")
     * Add a glossary link to the first instance of each glossary word in an article
     *
     * @param Request $request
     * @param BookArticlesEntity $article
     *
     * @return Response
     *
     */
    public function addglossarytoarticleAction(Request $request, BookArticlesEntity $article = null){
        if (!$this->hasPermission($this->name. '::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException($this->trans("You do not have permission to perform a glossary add."));
        }
        $response = $this->redirect($this->generateUrl('paustianbookmodule_admin_modifyarticle'));
        if (null == $article) {
            return $response;
        }
        $content = $article->getContents();
        //first we need to remove any glossary terms that are already there.
        $content =  preg_replace("/<a class=\"glossary\">(.*?)<\/a>/", "$1", $content);
        $glossRep = $this->getDoctrine()->getRepository('PaustianBookModule:BookGlossEntity');
        $glossTerms = $glossRep->getGloss("", null, null, ['u.term']);
        $totalCount = 0;
        foreach($glossTerms as $term){
            $content = preg_replace("|\b(" . $term['term'] . ")\b|", "<a class=\"glossary\">$1</a>", $content, 1, $count);
            $totalCount += $count;
        }
        $article->setContents($content);
        //persist the article
        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();
        $this->addFlash('status', $this->trans("$totalCount glossary terms were added."));
        return $response;
    }

    /**
     * I made this function to allow the respository to render the figure. I probably should not have
     * removed it from the controller and will put it back in a future version.
     *
     * @param string $template
     * @param array $parameters
     * @return mixed
     */
    public function renderFigure(string $template, array $parameters){
        return $this->render($template, $parameters);
    }


}