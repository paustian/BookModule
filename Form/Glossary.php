<?php
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Glossary extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('term', 'text')
            ->add('definition')
            ->add('save', 'submit', ['label' => __('Edit Glossary Term')]);
    }

    /**
     * @deprecated
     * @return string
     */
    public function getName()
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
