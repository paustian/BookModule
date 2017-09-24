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
#TODO this needs to be elminated and moved into the new link services interface. The user links too.
class AdminApi extends \Zikula_AbstractApi {

    public function getLinks() {
        $links = [];
        
        //The quesiton editing menu
        $submenulinks = array();
        $submenulinks[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_edit'),
                'text' => $this->__('Create New Book')); 
        $submenulinks[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_modify'),
                'text' => $this->__('Edit or Delete Book')); 
        
        $links[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_edit'),
                'text' => $this->__('Books'), 
                'icon' => 'book', 
                'links' => $submenulinks);
        
        $submenulinks3 = array();
        $submenulinks3[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_editchapter'),
                'text' => $this->__('Create New Chapter')); 
        $submenulinks3[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_modifychapter'),
                'text' => $this->__('Edit, Delete, Export, Search/Replace, or check URLS in Chapter')); 
        $submenulinks3[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_import'),
                'text' => $this->__('Import Chapter'));
        $links[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_editchapter'),
                'text' => $this->__('Chapters'), 
                'icon' => 'bookmark', 
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
        $links[] = array(
                'url' => $this->get('router')->generate('paustianbookmodule_admin_editfigure'),
                'text' => $this->__('Figures'), 
                'icon' => 'line-chart', 
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
                'icon' => 'book', 
                'links' => $submenulinks2);
       
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
        if (!$this->hasPermission($this->name. '::Chapter', "::", ACCESS_READ)) {
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

    
}

