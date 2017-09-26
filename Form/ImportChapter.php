<?php
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Zikula\Common\Translator\TranslatorInterface;

class ImportChapter extends AbstractType {
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * BlockType constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('importText', TextareaType::class, array('label' => $this->translator-> __('Chapter Text'), 'required' => false))
            ->add('save', SubmitType::class, array('label' => $this->translator->__('Import Chapter')));
    }

    public function getName()
    {
        return 'paustianbookmodule_importbook';
    }
}
