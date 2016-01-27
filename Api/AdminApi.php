<?php

// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: Timothy Paustian
// Purpose of file:  Book administration API
// ----------------------------------------------------------------------

namespace Paustian\BookModule\Api;

class AdminApi extends \Zikula_AbstractApi {

    public function getLinks() {
        $links = array();
        
        //The quesiton editing menu
        $submenulinks = array();
        $submenulinks[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_edit'),
                'text' => $this->__('Create New Book')); 
        $submenulinks[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_modify'),
                'text' => $this->__('Edit or Delete Book')); 
        $submenulinks[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_export'),
                'text' => $this->__('Import Chapter/Book'));
        $submenulinks[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_import'),
                'text' => $this->__('Export Chapter/Book')); 
        $links[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_edit'),
                'text' => $this->__('Books'), 
                'icon' => 'list', 
                'links' => $submenulinks);
        
        $submenulinks3 = array();
        $submenulinks3[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_editchapter'),
                'text' => $this->__('Create New Chapter')); 
        $submenulinks3[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_modifychapter'),
                'text' => $this->__('Edit or Delete Chapter')); 
        $links[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_editchapter'),
                'text' => $this->__('Chapters'), 
                'icon' => 'list', 
                'links' => $submenulinks3);
        
        $submenulinks4 = array();
        $submenulinks4[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_editarticle'),
                'text' => $this->__('Create New Article')); 
        $submenulinks4[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_modifyarticle'),
                'text' => $this->__('Edit or Delete Article'));
        $submenulinks4[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_arrangearticles'),
                'text' => $this->__('Arrange Articles'));
        $links[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_editarticle'),
                'text' => $this->__('Articles'), 
                'icon' => 'list', 
                'links' => $submenulinks4);
        
        $submenulinks5 = array();
        $submenulinks5[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_editfigure'),
                'text' => $this->__('Create New Figure')); 
        $submenulinks5[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_modifyfigure'),
                'text' => $this->__('Edit or Delete Figure')); 
        $submenulinks5[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_listfigures'),
                'text' => $this->__('List Figures')); 
        $links[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_editfigure'),
                'text' => $this->__('Figures'), 
                'icon' => 'list', 
                'links' => $submenulinks5);
        
      
        $submenulinks2 = array();
        $submenulinks2[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_editglossary'),
                'text' => $this->__('Create New Glossary Item')); 
        $submenulinks2[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_modifyglossary'),
                'text' => $this->__('Edit or Delete Glossary Item')); 
        $submenulinks2[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_importglossary'),
                'text' => $this->__('Import Glossary'));
        $submenulinks2[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_checkstudentdefs'),
                'text' => $this->__('Check for Requested Definitions'));
        $links[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_editglossary'),
                'text' => $this->__('Glossary'), 
                'icon' => 'refresh', 
                'links' => $submenulinks2);
        
        $submenulinks6 = array();
        $submenulinks6[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_checkurls'),
                'text' => $this->__('Check for Correct URLs')); 
        $submenulinks6[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_dosearchreplace'),
                'text' => $this->__('Global Search and Replace')); 
        $submenulinks6[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_modifyconfig'),
                'text' => $this->__('Modify Configuration'));
       
        $links[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_checkurls'),
                'text' => $this->__('Global Functions'), 
                'icon' => 'refresh', 
                'links' => $submenulinks6);
        return $links;
    }

    public function createhighlight($args) {

        //print "uid:$uid art:$aid, start:$start end:$end";die;
        // Argument check
        //we make sure that all the arguments are there
        //and that the chapter is larger than 1
        //negative chapters
        if ((!isset($args['uid'])) ||
                (!isset($args['aid'])) ||
                (!isset($args['start'])) ||
                (!isset($args['end']))) {
            LogUtil::addErrorPopup($this->__('Argument error in createhighlight.'));
            return false;
        }
        //now check to make sure the variables make sense
        //none of these is allowed to be less than 0
        if (($args['uid'] < 1) ||
                ($args['aid'] < 1) ||
                ($args['start'] < 0) ||
                ($args['end']) < 0) {
            LogUtil::addErrorPopup($this->__('Argument error in createhighlight.'));
            return false;
        }


        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::Chapter', "::", ACCESS_READ)) {
            LogUtil::registerPermissionError();
            return false;
        }
        //save the user data
        $userData = new Book_Entity_BookUserData();
        $userData->merge($args);
        $this->entityManager->persist($userData);
        $this->entityManager->flush();

        return true;
    }

    public function deletehighlight($args) {
        // Argument check
        if (!isset($args['udid'])) {
            LogUtil::addErrorPopup($this->__('Argument error in deletehighlight.'));
            return false;
        }

        //Delete the user data
        $highlight = $this->entityManager->getRepository('Book_Entity_BookUserData')->find($args['udid']);
        $this->entityManager->remove($highlight);
        $this->entityManager->flush();
        // Let the calling process know that we have finished successfully
        return true;
    }

    public function getemptyglossaryitems($args) {
        //security check
        if (!SecurityUtil::checkPermission('Book::Chapter', ".*::.*", ACCESS_EDIT)) {
            return false;
        }
        // Build the where clause
        $repository = $this->entityManager->getRepository('Book_Entity_BookGloss');
        $where = 'a.definition = \'\'';
        $items = $repository->getGloss('term', $where);
        // Return the item array
        return $items;
    }

    // TODO: Create a search and replace function that will walkthough and change all the articles. 
    // You should make it possble to do a preview of the first found article and display it. Have a
    // pattern field and a replace field where the data can be entere and a check box for preview.

    public function dosearchreplacebook($args) {
        //no securtiy check needed, handled at the chapter level
        if (!isset($args['bid']) || !isset($args['search_pat']) || !isset($args['replace_pat']) || !isset($args['preview'])) {
            LogUtil::addErrorPopup($this->__('Argument error in dosearchreplacebook.'));
            return true;
        }
        //grab all the chapters from the book
        $items = ModUtil::apiFunc('Book', 'user', 'getallchapters', array('bid' => $args['bid']));
        //check to make sure we got something
        if (!$items) {
            LogUtil::addErrorPopup($this->__('Argument error in dosearchreplacebook.'));
        }
        $return_text = "";
        foreach ($items as $item) {
            //call the serach and replace function on each chapter
            $args['cid'] = $item['cid'];
            $return_text .= $this->dosearchreplacechap($args);
        }
        return $return_text;
    }

    public function dosearchreplacechap($args) {
        if (!isset($args['cid']) || !isset($args['bid']) || !isset($args['search_pat']) || !isset($args['replace_pat']) || !isset($args['preview'])) {
            LogUtil::addErrorPopup($this->__('Argument error in dosearchreplacechap.'));
            return true;
        }
       

        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::Chapter', "$args[bid]::$args[cid]", ACCESS_EDIT)) {
            LogUtil::registerPermissionError();
            return false;
        }

        $art_items = ModUtil::apiFunc('Book', 'user', 'getallarticles', array('cid' => $args['cid']));
        if (!$art_items) {
            LogUtil::addErrorPopup($this->__('Argument error in dosearchreplacechap. Could not find articles'));
        }
        $preview_text = "";
        $search_pat = $args['search_pat'];

        $replace_pat = $args['replace_pat'];
        if ($args['preview']) {
            $replace_pat = "<b>$replace_pat</b>";
        }
        $repository = $this->entityManager->getRepository('Book_Entity_BookArticles');
                    
        foreach ($art_items as $item) {
            $count;
            $old = $item['contents'];
            //this is adding lots of <b> before and after for some reason. Like it is being search more than once time.
            if ($args['preview']) {
                $new = preg_replace($search_pat, $replace_pat, $old, -1, $count);
                if (!isset($new)) {
                    $search_pat = '~' . $search_pat . '~';
                    $new = preg_replace($search_pat, $replace_pat, $old, -1, $count);
                    if (!isset($new)) {
                        // there is an error in the serach pattern
                        return LogUtil::addErrorPopup($this->__('The search pattern for the regular expression is poorly formed.'));
                    }
                }
                if ($count > 0) {
                    $preview_text .= "<h3>" . $item['title'] . "</h3>\n<p>" . $new . "</p>";
                }
            } else {
                $new = preg_replace($search_pat, $replace_pat, $old, -1, $count);
                if (!isset($new)) {
                    $search_pat = '~' . $search_pat . '~';
                    $new = preg_replace($search_pat, $replace_pat, $old, -1, $count);
                    if (!isset($new)) {
                        // there is an error in the serach pattern
                        return LogUtil::addErrorPopup($this->__('The search pattern for the regular expression is poorly formed.'));
                    }
                }
                $item['contents'] = $new;
                if ($count > 0) {
                    //get the new article and merge the new data
                    $article = $repository->find($item['aid']);
                    $item_array = array();
                    $item_array['aid'] = $item['aid'];
                    $item_array['title'] = $item['title'];
                    $item_array['cid'] = $item['cid'];
                    $item_array['bid'] = $item['bid'];
                    $item_array['contents'] = $item['contents'];
                    $item_array['counter'] = $item['counter'];
                    $item_array['lang'] = $item['lang'];
                    $item_array['next'] = $item['next'];
                    $item_array['prev'] = $item['prev'];
                    $item_array['number'] = $item['number'];
                    
                    //Now fixed. You cannot call this function with an object, it must be an array
                    //and you cannot just cast it, it does funky things. So I had to manuall copy it.
                    $article->merge($item_array);
                }
            }
        }
        $this->entityManager->flush();
        return $preview_text;
    }

    public function addglossaryitems($args) {
        $aid = $args['aid'];
        //get the article
        $article = ModUtil::apiFunc('Book', 'user', 'getarticle', array('aid' => $aid));
        //add glossary terms
        $article['contents'] = $this->add_glossary_terms(array('in_text' => $article['contents']));
        //save the article, we can't just pass article because it is a object 
        //and does not traslate well to an array
        return ModUtil::apiFunc('book', 'admin', 'updatearticle', array('aid' => $article['aid'],
                    'title' => $article['title'],
                    'cid' => $article['cid'],
                    'bid' => $article['bid'],
                    'contents' => $article['contents'],
                    'counter' => $article['counter'],
                    'lang' => $article['lang'],
                    'next' => $article['next'],
                    'prev' => $article['prev'],
                    'number' => $article['number']));
    }

//glossary addition code



    public function add_glossary_terms($args) {
        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            LogUtil::registerPermissionError();
            return false;
        }
        //grab the incoming text
        $contents = $args['in_text'];
        //clean out any glossary item that are there.
        $pattern = "/<a class=\"glossary\" href.*?'\)\">(.*?)<\/a>/";
        $contents = preg_replace($pattern, "$1", $contents);

        //separate into key words and contents
        //we do not want to add glossary items to the key terms section
        $matches = array();
        if (preg_match('|(<ul class=\"key_words\">.*?</ul>)(.*)|s', $contents, $matches)) {
            $key_terms = $matches[1];
            $contents = $matches[2];
        }
        //grab all the glossary terms
        $glossary_terms = ModUtil::apiFunc('Book', 'user', 'getallglossary');
        $terms = array();
        //pack them into a list to use in the preg_replace_callback
        foreach ($glossary_terms as $term) {
            //This is a search pattern, hence the slash before and after
            //the \b denotes a word boundary, so the pattern will search for whole words
            //The i signifies case insensitive. we also add 30 characters before and after to
            //check for things we don't want
            //qualify any characters in the gloss definitions.
            $search_string = preg_quote($term['term']);
            $terms[] = "|(.{150}[^>])(\b$search_string\b)(.{150})|is";
        }

        //sort the terms, doing the biggest ones first
        usort($terms, array($this, _sort_term_sizes));
        //now search for all the terms
        $result = preg_replace_callback($terms, array($this, _glossreplacecallback), $contents, 1);
        if ($result != "") {
            $contents = $result;
        }

        return $key_terms . $contents . "\n";
    }

    /**
     * _sort_term_sizes
     * @param $a
     * @param $b
     * @return whether $a is longer than B
     *
     *  Private function called to sort the terms by size. We have to order them this way to prevent
     *  overlapping definitions. For example, if you have defined 'quarter note' and 'note'. You do not
     *  want note defined inside quarter note. The replace callback function, used after this, checks
     *  for this, but to get this to work, you need to order terms by size
     */
    private function _sort_term_sizes($a, $b) {

        $sizea = strlen($a);
        $sizeb = strlen($b);

        if ($sizea == $sizeb) {
            return 0;
        }
        return ($sizea < $sizeb) ? 1 : -1;
    }

    /**
     * _glossreplacecallback
     *
     * @param $matches
     * @return the replacement string
     * The function searches for patterns like (.{30})(glossterm)(.{30}). We scan before and after
     * to make sure that the match does not have class=glossary (this means it is already part of a
     * definition, a heading tag near it, is part of a table, and is not part of a link.
     * Note, right now this was
     */
    private function _glossreplacecallback($matches) {
//serach in the preceeding 30 characters for a glossary tag
//if the tag is there, then we do not want to define this one.
//this prevents overlapping glossary items
//no matches to items that are part of headings
        $pattern = "|</{0,1}h[1-6]>|";
        if (preg_match($pattern, $matches[0])) {
//if we find this in the preceeding string, we just return the whole match.
            return $matches[0];
        }
//no matches in tables
        $pattern = "|</{0,1}td>|";
        if (preg_match($pattern, $matches[0])) {
//if we find this in the preceeding string, we just return the whole match.
            return $matches[0];
        }
//no matches in tables
        $pattern = "|</{0,1}th>|";
        if (preg_match($pattern, $matches[0])) {
//if we find this in the preceeding string, we just return the whole match.
            return $matches[0];
        }
//make sure its not part of a link
        $pattern = "|index.php|";
        if (preg_match($pattern, $matches[0])) {
//if we find this in the preceeding string, we just return the whole match.
            return $matches[0];
        }
        $pattern = "|escape\(|";
        if (preg_match($pattern, $matches[0])) {
//if we find this in the preceeding string, we just return the whole match.
            return $matches[0];
        }
        $pattern = "|\'\)\">|";
        if (preg_match($pattern, $matches[0])) {
//if we find this in the preceeding string, we just return the whole match.
            return $matches[0];
        }

        $ret_text = $matches[1] . "<a class=\"glossary\">" . $matches[2] . "</a>" . $matches[3];

        return $ret_text;
    }

}

?>