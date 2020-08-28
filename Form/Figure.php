<?php

declare(strict_types=1);
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


/**
 * Description of Chapter
 * Set up the elements for a Chapter form.
 *
 * @author paustian
 * 
 */
class Figure extends AbstractType {

    /**
     * BlockType constructor.
     */
    public function __construct() {
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class, ['label' => 'Figure Title', 'required' => true]);
        $builder->add('fig_number', NumberType::class, ['label' => 'Figure Number', 'required' => true]);
        $builder->add('chap_number', NumberType::class, ['label' => 'Chapter Number', 'required' => true]);
        $builder->add('img_link', TextType::class, ['label' => 'Path to medium', 'required' => true]);
        $builder->add('perm', CheckboxType::class, ['label' => 'Permission granted', 'required' => false]);
        $builder->add('content', TextareaType::class, ['label' => 'Figure description', 'required' => true]);
        $builder->add('add', SubmitType::class, array('label' => 'Edit Figure'));
        
    }

    public function getName() :string
    {
        return 'paustianbookmodule_figure';
    }

    /**
     * OptionsResolverInterface is @deprecated and is supposed to be replaced by
     * OptionsResolver but docs not clear on implementation
     *
     * @param OptionsResolvere $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paustian\BookModule\Entity\BookFiguresEntity',
        ));
    }
}

