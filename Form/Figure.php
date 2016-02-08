<?php
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
/**
 * Description of Chapter
 * Set up the elements for a Chapter form.
 *
 * @author paustian
 * 
 */
class Figure extends AbstractType {
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text', ['label' => __('Figure Title'), 'required' => true]);
        $builder->add('fig_number', 'number', ['label' => __('Figure Number'), 'required' => true]);
        $builder->add('chap_number', 'number', ['label' => __('Chapter Number'), 'required' => true]);
        $builder->add('img_link', 'text', ['label' => __('Path to medium'), 'required' => true]);
        $builder->add('perm', 'checkbox', ['label' => __('Permission granted'), 'required' => false]);
        $builder->add('content', 'textarea', ['label' => __('Figure description'), 'required' => true]);
        $builder->add('add', 'submit', array('label' => 'Edit Figure'));
        
    }

    public function getName()
    {
        return 'paustianbookmodule_figure';
    }

    /**
     * OptionsResolverInterface is @deprecated and is supposed to be replaced by
     * OptionsResolver but docs not clear on implementation
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paustian\BookModule\Entity\BookFiguresEntity',
        ));
    }
}

