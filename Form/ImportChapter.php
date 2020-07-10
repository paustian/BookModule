<?php

declare(strict_types=1);
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Contracts\Translation\TranslatorInterface;

class ImportChapter extends AbstractType {

    /**
     * BlockType constructor.
     */
    public function __construct() {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('importText', TextareaType::class, ['label' => 'Chapter Text', 'required' => false])
            ->add('save', SubmitType::class, array('label' => 'Import Chapter'));
    }

    public function getName() : string
    {
        return 'paustianbookmodule_importbook';
    }
}
