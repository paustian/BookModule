<?php
namespace Paustian\BookModule;

use Zikula\Component\HookDispatcher\SubscriberBundle;

use HookUtil;
use Zikula\SearchModule\AbstractSearchable;

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
        $meta['capabilities'] = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true),
                                     AbstractSearchable::SEARCHABLE => array('class' => 'Paustian\BookModule\Helper\SearchHelper'));
        $meta['securityschema'] = array('PaustianBookModule::' => 'Book::Chapter');
        $meta['author'] = 'Timothy Paustian';
        $meta['contact'] = 'http://http://www.bact.wisc.edu/faculty/paustian/';
        
        return $meta;
    }

    protected function setupHookBundles(){ 
        $bundle = new SubscriberBundle($this->name, "subscriber.book.ui_hooks.book", "ui_hooks", $this->__("Book Display Hooks"));

        //code for setting up hooks with Zikula. The Book module can subcribe to modules providing hooks
        $bundle->addEvent('display_view', 'book.ui_hooks.book.display_view');
        $bundle->addEvent('process_edit', 'book.ui_hooks.book.process_edit');
        $bundle->addEvent('process_delete', 'book.ui_hooks.book.process_delete');
        $bundle->addEvent('form_edit', 'book.ui_hooks.book.form_edit');
        $bundle->addEvent('form_delete', 'book.ui_hooks.book.form_delete');
        $bundle->addEvent('validate_edit', 'book.ui_hooks.book.validate_edit');
        $bundle->addEvent('validate_delete', 'book.ui_hooks.book.validate_delete');
        $this->registerHookSubscriberBundle($bundle);
    }
}
