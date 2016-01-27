<?php
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\NullToEmptyTransformer;

class Article extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('contents')
            ->add($builder->create('lang', 'choice', array(
                'choices' => \ZLanguage::getInstalledLanguageNames(),
                'required' => false,
                'placeholder' => __('All')
                ))->addModelTransformer(new NullToEmptyTransformer()))
            ->add('next', 'number', ['label' => __('Next'), 'required' => true])
            ->add('prev', 'number', ['label' => __('Previous'), 'required' => true])
            ->add('number', 'number', ['label' => __('Article Order Number'), 'required' => true])
            ->add('save', 'submit', ['label' => __('Edit Article')]);
    }

    /**
     * @deprecated
     * @return string
     */
    public function getName()
    {
        return 'paustianbookmodule_article';
    }

    public function getBlockPrefix()
    {
        return 'paustianbookmodule_article';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Paustian\BookModule\Entity\BookArticlesEntity',
        ]);
    }

}
