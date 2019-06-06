<?php
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;

class Glossary extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * BlockType constructor.
     * @param TranslatorInterface $translator
     * @param LocaleApiInterface $localeApi
     */
    public function __construct(
        TranslatorInterface $translator)   {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('term', TextType::class)
            ->add('definition', TextareaType::class)
            ->add('save', SubmitType::class, ['label' =>$this->translator-> __('Edit Glossary Term')]);
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
