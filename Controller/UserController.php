<?php

declare(strict_types=1);

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: Timothy Paustian
// Purpose of file:  Book user display functions
// ----------------------------------------------------------------------

namespace Paustian\BookModule\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\RouteUrl;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Paustian\BookModule\Entity\BookEntity;
use Paustian\BookModule\Entity\BookArticlesEntity;
use Paustian\BookModule\Entity\BookFiguresEntity;
use Paustian\BookModule\Entity\BookChaptersEntity;
use Paustian\BookModule\Entity\BookGlossEntity;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Paustian\BookModule\API\TextSummarizer;


class UserController extends AbstractController {

    /**
     * @var CurrentUserApiInterface
     */
    var $currentUserApi;

    /**
     * @private TextSummarizer
     * a link to the class that uses TextRankFacade to summarize text.
     */
    private $textSummarizer;

    public function __construct(
        AbstractExtension $extension,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        TranslatorInterface $translator,
        CurrentUserApiInterface $currentUserApi) {
        parent::__construct($extension, $permissionApi, $variableApi, $translator);
        $this->currentUserApi = $currentUserApi;
        $this->textSummarizer = new TextSummarizer();
        //initially don't summarize the text at all.
        $this->textSummarizer->setSumLevel(1, (int)$this->getVar('sumlevel'));
    }

    /**
     * @Route("")
     * 
     * @param $request
     * @return Response
     */
    public function index(Request $request) : Response {
        // Security check
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_READ)) {
            throw new AccessDeniedException($this->trans('You do not have pemission to access any books.'));
        }

        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookEntity');
        $books = $repo->getBooks();
        $test = $this->render('@PaustianBookModule/User/book_user_books.html.twig', ['books' => $books]);
        return $test;
    }

    /**
     * @Route("/toc/{book}")
     *
     * @param Request $request
     * @param BookEntity $book
     * @return Response
     */
    public function toc(Request $request, BookEntity $book = null) : Response {
        $bid = -1;
        if (null === $book) {
            $bid = $request->query->getInt('bid');
            if (!isset($bid)) {
                return $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
            }
        } else {
            $bid = $book->getBid();
        }

        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookEntity');
        $booktoc = $repo->buildtoc($bid);

        //we can simplifiy this quite a bit since we only need 1 book.
        $bookData = $booktoc[0];

        //We need to walk the chapters and elminate the ones that you cannot read
        foreach ($bookData['chapters'] as &$chapter) {
            if ($chapter['number'] < 0) {
                $chapter['print'] = 0; //don't show it.
                continue;
            }
            if ($this->hasPermission($this->name . '::Chapter', $bookData['bid'] . '::' . $chapter['cid'], ACCESS_READ)) {
                $chapter['print'] = 1; //show it and have link to the item
            } else {
                $chapter['print'] = 2; //show it, but no link
            }
        }

        return $this->render('@PaustianBookModule/User/book_user_toc.html.twig', ['book' => $bookData]);
    }

    /**
     * @Route("/view")
     * @param Request $request
     * @return Response
     */
    public function view(Request $request) : Response {
        return $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
    }

    /**
     * @Route("/display")
     * @return Response
     */
    public function display(Request $request) : Response {
        return $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
    }

    /**
     * @Route("/displayarticle/{article}")
     *
     * @param Request $request
     * @param BookArticlesEntity $article
     * @param bool $doglossary
     * @return Response
     */
    public function displayarticle(Request $request,
                                         BookArticlesEntity $article = null,
                                         bool $doglossary = true) : Response {
        if (null === $article) {
            $aid = $request->get('aid');
            if (isset($aid)) {
                $article = $this->getDoctrine()->getRepository('PaustianBookModule:BookArticlesEntity')->find($aid);
            }
            if (null === $article) {
                return $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
            }
        }
        //get the chapter title
        $cid = $article->getCid();
        $aid = $article->getAid();
        if (!$this->hasPermission($this->name . '::Chapter', $article->getBid() . "::$cid", ACCESS_READ)) {
            throw new AccessDeniedException($this->trans('You do not have pemission for this chapter.'));
        }
        $doc = $this->getDoctrine();
        $chapter = $doc->getRepository('PaustianBookModule:BookChaptersEntity')->find($cid);
        $chnumber = 'U';
        if (isset($chapter)) {
            $chnumber = $chapter->getNumber();
        }

        $content = $article->getContents();
        //Now add the highlights if necessary
        $uid = $this->currentUserApi->get('uid');
        $summarize = $this->getVar('summarize');
        $maxLevel = $this->getVar('sumlevel');
        //Only process highlights if the text summarizing functions are off or the sumLevel is at 5 sentences (max)
        if (($uid != "") && (!$summarize || ($this->textSummarizer->getSumLevel() == 5))) {
            //procesing the highlights goes here. This way the figure text won't matter
            $content = $this->_process_highlights($content, $article->getAid(), $this->currentUserApi);
        }

        //this code is used for the hook
        $return_url = new RouteUrl('paustianbookmodule_user_displayarticle', array('aid' => $aid));

        //call the user api to increment the counter
        $doc->getRepository('PaustianBookModule:BookArticlesEntity')->incrementCounter($article);

        $show_internals = false;
        if ($this->hasPermission($this->name . '::Chapter', $article->getBid() . "::$cid", ACCESS_EDIT)) {
            $show_internals = true;
        }

        $return_text = $this->render('@PaustianBookModule/User/book_user_displayarticle.html.twig', ['article' => $article,
                    'chnumber' => $chnumber,
                    'content' => $content,
                    'return_url' => $return_url,
                    'summarize' => $summarize,
                    'max_level' => $maxLevel,
                    'show_internals' => $show_internals])->getContent();

        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookArticlesEntity');
        $return_text = $repo->addfigures($return_text, $this);

        //work in the glossary items
        if ($doglossary) {
            $return_text = $this->_add_glossary_defs($return_text);
        }
        if($summarize){
            $return_text = $this->textSummarizer->summarizeText($return_text);
        }
        return new Response($return_text);
    }

    /**
     * book_add_glossary_defs
     *
     * Given some text, insert the glossary definitions.
     *
     * @param string inText - the text to add glossary definitions to
     * @return string retText - the text with glossary definitions addeed
     *
     */
    private function _add_glossary_defs(string $in_text) {
        //all the work is done in this funcion
        $pattern = "|<a class=\"glossary\"[^>]*>(.*?)</a>|";
        $ret_text = preg_replace_callback($pattern, array(&$this, "_glossary_add"), $in_text);

        return $ret_text;
    }

    /**
     *  glossary_add
     * This is a callback function to convert glossary terms into their definitions.
     * I am added it here and not into the text to keep from polluting the text
     * with glossary definitions.
     *
     * @param $matches
     * @return string
     */
    private function _glossary_add(array $matches) : string {
        $inTerm = $matches[1];
        $item = array();

        $where['cond'] = "u.term=:term";
        $where['paramkey'] = 'term';
        $where['paramval'] =$inTerm;

        $item = $this->getDoctrine()->getRepository('PaustianBookModule:BookGlossEntity')->getGloss('', null, $where);
        if (count($item) == 0) {
            //This did not work, try searching for match instead
            //->where('a.title LIKE :title')
            //   ->setParameter('title', '%'.$data['search'].'%')
            $where['cond'] = "u.term LIKE ?1";
            $where['paramkey'] = '1';
            $where['paramval'] = '%' . $inTerm . '%';
            $item = $this->getDoctrine()->getRepository('PaustianBookModule:BookGlossEntity')->getGloss('', null, $where);
        }
        // Check for an error and if so
        //just return. This is not an error, we just won't replace it
        //$matches[0] contains the found string, so we just return the found
        //string.
        if (count($item) == 0) {
            return $matches[0];
        }
        $definition = $item[0]['definition'];
        $lcterm = strtolower($inTerm);
        $url = $this->generateUrl('paustianbookmodule_user_displayglossary') . "#$lcterm";
        $ret_text = "<a class=\"glossary\" data-html=\"true\" href=\"$url\" title=\"$definition\">$inTerm</a>";
        return $ret_text;
    }

    /**
     * process_highlights
     *
     * Add highlight to the incomping text, based upon the offsets in the highlights array
     * @param string $content
     * @param int $aid
     * @param CurrentUserApiInterface $currentUserApi
     * @return string
     */
    private function _process_highlights(string $content,
                                         int $aid,
                                         CurrentUserApiInterface $currentUserApi)
    {//A modifier that has to go in to account for
        $uid = $currentUserApi->get('uid');
        if ($uid == "") {
            return $content;
        }

        $userRepo = $this->getDoctrine()->getRepository('PaustianBookModule:BookUserDataEntity');

        $highlights = $userRepo->getHighlights($aid, $uid);
        //inserted <span> tags from other highlighting.
        $adjust = 0;
        foreach ($highlights as $hItem) {
            $hStart = $hItem->getStart();
            $hEnd = $hItem->getEnd();
            //grab the text we need to highlight
            $mid_text = substr($content, $hStart + $adjust, $hEnd - $hStart);
            //search first for <p> tags and add a <p><span> tag
            //note that $ps_count is the number of times it was replaced.
            //print "<b>text before:</b> $mid_text <br />";
            $pattern = '/(<p>)/';
            $replacement = '$1<span class="highlight">';
            $matches = array();
            $ps_count = 0;
            $ps_count = preg_match_all($pattern, $mid_text, $matches);
            if ($ps_count != 0) {
                $mid_text = preg_replace($pattern, $replacement, $mid_text);
            }
            //print "<b>count:</b>$pscount <br /><b>text after open paragraph tag:</b> $mid_text <br />";
            //now search for </p> and add </span></p> tags
            $pe_count = 0;
            $pattern = '/<\/p>/';
            $replacement = "<!--highlight--></span></p>";
            $pe_count = preg_match_all($pattern, $mid_text, $matches);
            if ($pe_count != 0) {
                $mid_text = preg_replace($pattern, $replacement, $mid_text);
            }
            //print "<b>text after close paragraph tag:</b> $mid_text <br />";die;

            $content = substr($content, 0, $hStart + $adjust) . "<span class=\"highlight\">" .
                    $mid_text . "<!--highlight--></span>" .
                    substr($content, $hEnd + $adjust, strlen($content) - $hEnd);
            $adjust += 47 + (24 * $ps_count) + (23 * $pe_count);
        }
        //I need to add a little form on the end for pages that have highlight.
        //This form would contain the id of the php item
        return $content;
    }



    /**
     * @Route("/displayfigure/{figure}")
     *
     * @param Request $request
     * @param BookFiguresEntity $figure
     * @return Response
     */
    public function displayfigure(Request $request, BookFiguresEntity $figure = null) : Response{
        if (null === $figure) {
            return $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
        }
        if (!$this->hasPermission($this->name . '::', $figure->getBid() . "::", ACCESS_OVERVIEW)) {
            throw new AccessDeniedException($this->trans('You do not have pemission to access any figures.'));
        }
        //we look for these parameters in the query string. If they are not there
        //they are set to defaults.
        if(!($width = $request->query->get('width'))){
            $width = 0;
        }
        if(!($height = $request->query->get('height'))){
            $height = 0;
        }
        if(!($movName = $request->query->get('movName'))){
            $movName = 'movieName';
        }
        if(!($weight = $request->query->get('weight'))){
            $weight = 101;
        }

        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookArticlesEntity');

        $figureText = $repo->_renderFigure($figure, $width, $height, true, $movName, $this, $weight);
        return new Response($figureText);
    }

    /**
     * @Route("/sumlevelchange", options={"expose"=true})
     * @Method("POST")
     *
     * Grab all comments associated with this module and item ID and return them to the caller
     * The caller is a javascript, see the javascripts in Resources/public/js directory
     *
     * @param Request $request
     * @return JsonResponse|AccessDeniedException
     */
    public function sumlevelchange(Request $request): JsonResponse{
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_READ)) {
            return new AccessDeniedException($this->trans('Access forbidden since you cannot read this page.'));
        }
        //get the summary level and set it
        $summaryLevel = (int)$request->get('sumLevel');
        $this->textSummarizer->setSumLevel($summaryLevel, (int)$this->getVar('sumlevel'));

        //now we have to rerender the text. The $request already has the aid of the article
        $response = $this->displayarticle($request);
        $jsonReply = ['html' => $response->getContent(),
                        'sumLevel' => $summaryLevel];
        return  new JsonResponse($jsonReply);
    }

    /**
     * @Route("/displayglossary")
     *
     * @param Request $request
     * @return Response
     */
    public function displayglossary(Request $request) : Response {
//you must have permission to read some book.
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException($this->trans('You do not have pemission to access any glossry items.'));
        }

        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookGlossEntity');
        $gloss_data = $repo->getGloss('', ['col' => 'u.term', 'direction' => 'ASC']);

        return $this->render('@PaustianBookModule/User/book_user_glossary.html.twig', ['glossary' => $gloss_data]);
    }

    /**
     * @Route("/displaybook/{book}")
     * Display the entire book. Note this should only be called locally and never online. It's a memory hog.
     * The purpose is to just get all of this.
     * @param Request $request
     * @param BookEntity $book
     * @return Response
     */
    public function displaybook(Request $request, BookEntity $book) : Response {
        $bid = -1;
        if (null === $book) {
            $bid = $request->get('bid');
            if (!isset($bid)) {
                return $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
            }
        } else {
            $bid = $book->getBid();
        }
        if (!$this->hasPermission($this->name . '::', "$bid::", ACCESS_READ)) {
            throw new AccessDeniedException($this->trans('You do not have pemission to access this book.'));
        }


        $ret_text = "";
        $chapRepo = $this->getDoctrine()->getRepository('PaustianBookModule:BookChaptersEntity');
        $chapters = $chapRepo->getChapter($bid);
        //now iterate through each chapter and call display_chapter
        foreach ($chapters as $chap_item) {
            $cid = $chap_item->getCid();
            if ($this->hasPermission($this->name . '::Chapter', "$bid::$cid", ACCESS_READ)) {
                $ret_text .= $this->displaychapter($request, $chap_item);
            }
        }
        return $ret_text;
    }

    /**
     * @Route("/displaychapter/{chapter}")
     *
     * @param Request $request
     * @param BookChaptersEntity $chapter
     * @return Response
     */
    public function displaychapter(Request $request, BookChaptersEntity $chapter = null) : Response {
        if (null === $chapter) {
            //Old style URL, look for the chapter using the cid
            $cid = $request->get('cid');
            $chapter = $this->getDoctrine()->getRepository('PaustianBookModule:BookChaptersEntity')->find($cid);
            if (null === $chapter) {
                return $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
            }
        }
        $cid = $chapter->getCid();
        $bid = $chapter->getBid();
        //grab the chapter data
        if (!$this->hasPermission($this->name . '::Chapter', "$bid::$cid", ACCESS_READ)) {
            throw new AccessDeniedException($this->trans('You do not have pemission to access the contents of this chapter.'));
            ;
        }
        $artRepo = $this->getDoctrine()->getRepository('PaustianBookModule:BookArticlesEntity');

        //grab all the articles and arrange them by number and we do not need the content.
        $articles = $artRepo->getArticles($cid, true, false);

        //process all inline figures.
        return $this->render('@PaustianBookModule/User/book_user_displaychapter.html.twig', ['chapter' => $chapter,
                    'articles' => $articles]);
    }

    /**
     * @Route("/displayarticlesinchapter/{chapter}")
     *
     * @param Request $request
     * @param BookChaptersEntity $chapter
     * @return Response
     */
    public function displayarticlesinchapter(Request $request, BookChaptersEntity $chapter = null) : Response {
        if (null === $chapter) {
            $cid = $request->get('cid');
            if (isset($cid)) {
                $chapter = $this->getDoctrine()->getRepository('PaustianBookModule:BookChaptersEntity')->find($cid);
            }
            if (null === $chapter) {
                return $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
            }
        }
        $cid = $chapter->getCid();
        $bid = $chapter->getBid();
        //grab the chapter data
        if (!$this->hasPermission($this->name . '::Chapter', "$bid::$cid", ACCESS_READ)) {
            throw new AccessDeniedException($this->trans('You do not have pemission to access the contents of this chapter.'));
            ;
        }
        $artRepo = $this->getDoctrine()->getRepository('PaustianBookModule:BookArticlesEntity');

        //grab all the articles and arrange them by number and we need the content.
        //Because we want the whole object so we can iterate it.
        $articles = $artRepo->getArticles($cid, true, true);


        //since we are viewing each article, increment the counter.
        foreach ($articles as $article_item) {
            $artRepo->incrementCounter($article_item);
        }

        //process all inline figures.
        $return_text = $this->render('@PaustianBookModule/User/book_user_displayarticlesinchapter.html.twig', ['chapter' => $chapter,
            'articles' => $articles])->getContent();
        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookArticlesEntity');
        $return_text = $repo->addfigures($return_text, $this);
        $return_text = $this->_add_glossary_defs($return_text);
        return new Response($return_text);
    }

    /**
     * @Route("/collecthighlights")
     *
     * Take all the highlights that a user has highlight for the book
     * and then display them to the user. This should be a useful study tool
     * @param Request $request
     * @return Response
     */
    public function collecthighlights(Request $request, BookArticlesEntity $article = null) :Response
    {
        //build an organization of the book
        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookEntity');
        $books = null;
        if ($article != null) {
            $books = $repo->buildtoc($article->getBid());
            //we don't want unassociated articles
            unset($books['undcl']);
        } else {
            $books = $repo->buildtoc();
        }

        return $this->render('@PaustianBookModule/User/book_admin_collecthighlights.html.twig', ['books' => $books]);
    }

    /**
     * @Route("/studypage")
     *
     * @param Request $request
     * @return Response
     */
    public function studypage(Request $request) : Response {
        $response = $this->redirect($this->generateUrl('paustianbookmodule_user_collecthighlights'));
        $uid = $this->currentUserApi->get('uid');
        if ($uid == "") {
            $this->addFlash('status', $this->trans('You must be logged and have access the study pages.'));
            return $response;
        }
        $em = $this->getDoctrine()->getManager();
        $articleIds = $request->get('aids');
        if (!isset($articleIds)) {
            $this->addFlash('status', $this->trans('You need to select some articles for the highlighted areas to be collected..'));
            return $response;
        }
        $articles = $em->getRepository('PaustianBookModule:BookArticlesEntity')->findBy(['aid' => $articleIds]);


        $highlightArray = array();
        $highlightInfo = array();
        $userDRepo = $em->getRepository('PaustianBookModule:BookUserDataEntity');
        $chapRepo = $em->getRepository('PaustianBookModule:BookChaptersEntity');
        $chapId = -10;
        $chapter = null;
        foreach ($articles as $art) {
            //grab the highlight data for this user.
            $artHighlights = $userDRepo->getHighlights($art->getAid(), $uid);
            if ($artHighlights) {
                foreach ($artHighlights as $hItem) {
                    $artChapId = $art->getCid();
                    if ($artChapId != $chapId) {
                        $chapId = $artChapId;
                        $chapter = $chapRepo->find($chapId);
                    }
                    $contents = $art->getContents();
                    $highlightInfo['content'] = \substr($contents, $hItem['start'], $hItem['end'] - $hItem['start']);
                    $highlightInfo['chapNum'] = $chapter->getNumber();
                    $highlightInfo['artNum'] = $art->getNumber();
                    $highlightInfo['aid'] = $art->getAid();
                    $highlightInfo['title'] = $art->getTitle();
                    $highlightArray[] = $highlightInfo;
                }
            }
        }
        return $this->render('@PaustianBookModule/User/book_user_studypage.html.twig', ['highlights' => $highlightArray]);
    }

    /**
     * @Route("/customizeText/{article}")
     *
     * The user has presumably selected some text. Act on it according to
     * what text was selected.
     *
     * @param Request $request
     * @param BookArticlesEntity $article
     *
     * @return boolean|RedirectResponse|Response
     */
    public function customizeText(Request $request,
                                        BookArticlesEntity $article) : Response {
        $button = $request->get('buttonpress');
        $text = $request->get('text');
        if ($button == 'highlight') {
            return $this->_doHighlight($request, $article, $text, $this->currentUserApi);
        } elseif ($button == 'dodef') {
            return $this->_dodef($request, $article, $text, $this->currentUserApi);
        } else {
            return $this->collecthighlights($request, $article);
        }
    }

    /**
     * Take the selection and make note of where it is. When it is displayed
     * the selected text will be highlighted yellow
     * @param Request $request
     * @param BookArticlesEntity $article
     * @param string $inText
     * @param CurrentUserApiInterface $currentUserApi
     * @return RedirectResponse
     */
    private function _doHighlight(Request $request, BookArticlesEntity $article,
                                  string $inText,
                                  CurrentUserApiInterface $currentUserApi) :RedirectResponse
    {
        $response = $this->redirect($this->generateUrl('paustianbookmodule_user_displayarticle', [ 'article' => $article->getAid()]));

        if ($inText == "") {
            $this->addFlash('status', $this->trans('You must choose a selection to before clicking the button.'));
            return $response;
        }
        if (!$this->hasPermission($this->name . '::Chapter', $article->getBid() . "::" . $article->getCid(), ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $uid = $currentUserApi->get('uid');
        if ($uid == "") {
            //user id is empty, we are not in
            $this->addFlash('status', $this->trans('You are not logged in. In this case you cannot add highlights.'));
            return $response;
        }

        $content = $article->getContents();

        //find the offsets
        //get rid of any newlines in the content and in the in text.
        //$content = preg_replace('/[\n|\r]/', ' ', $content);
        //$inText = preg_replace('/[\n|\r]/', ' ', $inText);
        //Scrub out the glooary stuff. out because this is not in the stored text
        $inText = preg_replace('|<a class="glossary".*?>|', '<a class="glossary">', $inText);
        //we also need to get rid of any highlights that are in the text
        $inText = preg_replace('|<span class="highlight".*?>|', '', $inText);
        $inText = preg_replace('|<!--highlight--></span>|', '', $inText);
        //Get the first 40 characters and then quote out anythying you might need be in them
        //40 characters should be unique to the text.
        $front_text = preg_quote(substr($inText, 0, 100));

        if (!preg_match("|$front_text|", $content, $matches, PREG_OFFSET_CAPTURE)) {
            $this->addFlash('status', $this->trans('You cannot highligh that text. You cannot highlight figure text. You might also try a slightly different selection.'));
            return $response;
        }
        $start = $matches[0][1];
        $end = $start + strlen($inText);

        if ($end == 0 || ($start > $end)) {

            //print "Start: $start, End: $end <br />";
            $this->addFlash('status', $this->trans('You cannot highlight that text. You cannot highlight figure text. You might also try a slightly different selection.'));
            return $response;
        }

        $aid = $article->getAid();

        //finally make sure that this area is not already highlighted.
        //if it is, unhighlight the area.
        $userRepo = $this->getDoctrine()->getRepository('PaustianBookModule:BookUserDataEntity');


        if (!$userRepo->checkHighlights($aid, $uid, $start, $end)) {
            $userRepo->recordHighlight($aid, $uid, $start, $end);
        }

        //finally redirect to the page again, this time with highlights
        return $response;
    }

    /**
     * Take the selection and if it is not defined and passes a few other criteria
     * add a glossary item to be defined.
     * @param Request $request
     * @param BookArticlesEntity $article
     * @param string $inTerm
     * @param CurrentUserApiInterface $currentUserApi
     * @return RedirectResponse
     */
    private function _dodef(Request $request, BookArticlesEntity $article,
                            string $inTerm,
                            CurrentUserApiInterface $currentUserApi) :RedirectResponse
    {

        $url = $this->generateUrl('paustianbookmodule_user_displayarticle', [ 'article' => $article->getAid()]);
        $response = $this->redirect($url);
        $uid = $currentUserApi->get('uid');
        if ($inTerm == "") {
            $this->addFlash('status', $this->trans("No word was selected to be defined"));
            return $response;
        }
        if ($uid == "") {
            //user id is empty, we are not in
            $this->addFlash('status', $this->trans('You are not logged in. In this case you cannot ask for definitions.'));
            return $response;
        }
        $inTerm = trim($inTerm);
        if (str_word_count($inTerm) > 3) {
            $this->addFlash('status', $this->trans('Terms to be defined must be 3 words or less. You may have also selected a term that is already defined.'));
            return $response;
        }
        //is it already defined?
        $glossRepo = $this->getDoctrine()->getRepository('PaustianBookModule:BookGlossEntity');
        $glossItem = $glossRepo->getTerm($inTerm);
        if ($glossItem) {
            //if the term has been defined, then send them to that term
            return $this->redirect($this->generateUrl('paustianbookmodule_user_displayglossary') . "#$inTerm");
        }
        //ok, we passed the checks. Add the word to the glossary
        $em = $this->getDoctrine()->getManager();
        $glossTerm = new BookGlossEntity();
        $glossTerm->setTerm($inTerm);
        $glossTerm->setDefinition("TBD");
        $glossTerm->setUser((string)$uid);
        $glossTerm->setUrl($url);
        $em->persist($glossTerm);
        $em->flush();
        $this->addFlash('status', $this->trans('Thank you for submitting this word. The authors will define it soon.'));
        return $response;
    }

    /**
     * @Route("/download")
     * @param Request $request
     * @return Response
     */
    public function download(Request $request) : Response
    {
        $allow_dl = false;
        if ($this->currentUserApi->isLoggedIn()) {
            $groups = $this->currentUserApi->get('groups');
            //I need to fix this!
            foreach ($groups as $gid => $group) {
                if($gid == 3){
                    $allow_dl = true;
                    break;
                }
            }
        }
        return $this->render('@PaustianBookModule/User/book_user_download.html.twig', ['allow_dl' => $allow_dl]);
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
