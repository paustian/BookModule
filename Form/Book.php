<?php
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
/**
 * Description of ExamForm
 * Set up the elements for a Exam form.
 *
 * @author paustian
 * 
 */
class Book extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => __('Book Name'), 'required' => true))
            ->add('add', 'submit', array('label' => 'Add Book'));
        
    }

    public function getName()
    {
        return 'paustianbookmodule_book';
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
            'data_class' => 'Paustian\BookModule\Entity\BookEntity',
        ));
    }
}

