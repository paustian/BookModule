<?php
// pnversion.php,v 1.7 2007/03/17 02:02:53 paustian Exp

// The following information is used by the Modules module 
// for display and upgrade purposes
// the version string must not exceed 10 characters!
class Book_Version extends Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['version']        = '2.1.0';
        $meta['displayname']    = $this->__('Book Writing');
        $meta['description']    = $this->__('A module for displying a large structured document, creating figure descriptions for the book, and a glossary.');
        // this defines the module's url and should be in lowercase without space
        $meta['url']            = $this->__('book');
        $meta['core_min'] = '1.3.0'; // Fixed to 1.3.x range
        $meta['core_max'] = '1.3.99'; // Fixed to 1.3.x range
        $meta['capabilities']   = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true));
        
        $meta['securityschema'] = array('Book::Chapter' => 'Book id (int)::Chapter id (int)');
        $meta['author'] = 'Timothy Paustian';
        $meta['contact'] = 'http://http://www.bact.wisc.edu/faculty/paustian/';
        
        return $meta;
    }

    protected function setupHookBundles()
    {
        //code for setting up hooks with Zikula. The Book module can subcribe to modules providing hooks
        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.book.ui_hooks.articles', 'ui_hooks', $this->__('Book Articles Hooks'));
        $bundle->addEvent('display_view', 'book.ui_hooks.articles.display_view');
        $bundle->addEvent('process_edit', 'book.ui_hooks.articles.process_edit');
        $bundle->addEvent('process_delete', 'book.ui_hooks.articles.process_delete');
        $this->registerHookSubscriberBundle($bundle);
    }
}
?>