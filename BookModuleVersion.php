<?php
namespace Paustian\BookModule;

use Zikula\Component\HookDispatcher\SubscriberBundle;

use HookUtil;

class BookModuleVersion extends \Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['name'] = __('Book');
        $meta['version'] = '3.0.0';
        $meta['displayname'] = __('Book');
        $meta['description'] = __('A module for displying a large structured document, creating figure descriptions for the book, and a glossary.');
        // this defines the module's url and should be in lowercase without space
        $meta['url'] = $this->__('book');
        $meta['core_min'] = '1.4.0'; // Fixed to 1.3.x range
        $meta['capabilities'] = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true));
        
        $meta['securityschema'] = array('PaustianBookModule::' => 'Book::Chapter');
        $meta['author'] = 'Timothy Paustian';
        $meta['contact'] = 'http://http://www.bact.wisc.edu/faculty/paustian/';
        
        return $meta;
    }

    protected function setupHookBundles()
    {
        //code for setting up hooks with Zikula. The Book module can subcribe to modules providing hooks
        $bundle = new SubscriberBundle($this->name, 'subscriber.book.ui_hooks.articles', 'ui_hooks', $this->__('Book Articles Hooks'));
        $bundle->addEvent('display_view', 'book.ui_hooks.articles.display_view');
        $bundle->addEvent('process_edit', 'book.ui_hooks.articles.process_edit');
        $bundle->addEvent('process_delete', 'book.ui_hooks.articles.process_delete');
        $bundle->addEvent('form_edit', 'book.ui_hooks.articles.form_edit');
        $bundle->addEvent('form_delete', 'book.ui_hooks.articles.form_delete');
        $bundle->addEvent('validate_edit', 'book.ui_hooks.articles.validate_edit');
        $bundle->addEvent('validate_delete', 'book.ui_hooks.articles.validate_delete');
        $this->registerHookSubscriberBundle($bundle);
    }
}
?>