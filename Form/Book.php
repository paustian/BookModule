<?php
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
        $trans = $options['translator'];
        $builder
            ->add('name',TextType::class, array('label' => $trans->__('Book Name'), 'required' => true))
            ->add('add', SubmitType::class, array('label' => $trans->__('Add Book')));
        
    }

    public function getBlockPrefix()
    {
        return 'paustianbookmodule_book';
    }

    /**
     * OptionsResolver but docs not clear on implementation
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paustian\BookModule\Entity\BookEntity',
        ));
    }
}

