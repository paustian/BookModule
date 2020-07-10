<?php

declare(strict_types=1);
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Contracts\Translation\TranslatorInterface;
/**
 * Set up the elements for a SearchReplace form.
 *
 * @author paustian
 * 
 */
class SearchReplace extends AbstractType {

    /**
     * BlockType constructor.
     */
    public function __construct() {
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('searchText', TextType::class, ['label' => 'Search Text', 'required' => true]);
        $builder->add('replaceText', TextType::class, ['label' => 'Replace Text', 'required' => true]);
        $builder->add('preview', CheckboxType::class, ['label' => 'Show a preview of the Replace', 'required' => false]);
        $builder->add('search', SubmitType::class, array('label' => 'Search'));
        
    }

    public function getName() :string
    {
        return 'paustianbookmodule_searchreplace';
    }
}

