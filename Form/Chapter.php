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
class Chapter extends AbstractType {
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', ['label' => __('Chapter Name'), 'required' => true]);
        $builder->add('number', 'number', ['label' => __('Chapter Number'), 'required' => true]);
        $builder->add('add', 'submit', array('label' => 'Edit Chapter'));
        
    }

    public function getName()
    {
        return 'paustianbookmodule_chapter';
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
            'data_class' => 'Paustian\BookModule\Entity\BookChaptersEntity',
        ));
    }
}

