<?php
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Zikula\CategoriesModule\Form\Type\CategoryType;

class ImportGloss extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('importText', 'textarea', array('label' => __('Glossary Text'), 'required' => false))
            ->add('save', 'submit', array('label' => 'Import Glossary Items'));
    }

    public function getName()
    {
        return 'paustianbookmodule_importgloss';
    }
}
