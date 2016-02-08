<?php
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
/**
 * Set up the elements for a SearchReplace form.
 *
 * @author paustian
 * 
 */
class SearchReplace extends AbstractType {
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('searchText', 'text', ['label' => __('Search Text'), 'required' => true]);
        $builder->add('replaceText', 'text', ['label' => __('Replace Text'), 'required' => true]);
        $builder->add('preview', 'checkbox', ['label' => __('Show a preview of the Replace'), 'required' => false]);
        $builder->add('search', 'submit', array('label' => 'Search'));
        
    }

    public function getName()
    {
        return 'paustianbookmodule_searchreplace';
    }
}

