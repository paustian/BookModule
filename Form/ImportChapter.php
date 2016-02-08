<?php
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Zikula\CategoriesModule\Form\Type\CategoryType;

class ImportChapter extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('importText', 'textarea', array('label' => __('Chapter Text'), 'required' => false))
            ->add('save', 'submit', array('label' => 'Import Chapter'));
    }

    public function getName()
    {
        return 'paustianbookmodule_importbook';
    }
}
