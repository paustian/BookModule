<?php

declare(strict_types=1);
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Glossary extends AbstractType
{
    /**
     * BlockType constructor.
     */
    public function __construct()   {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('term', TextType::class)
            ->add('definition', TextareaType::class)
            ->add('save', SubmitType::class, ['label' =>'Edit Glossary Term']);
    }

    /**
     * @deprecated
     * @return string
     */
    public function getName() : string
    {
        return 'paustianbookmodule_glossary';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Paustian\BookModule\Entity\BookGlossEntity',
        ]);
    }

}
