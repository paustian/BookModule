<?php

declare(strict_types=1);
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ImportGloss extends AbstractType {

    /**
     * BlockType constructor.
     */
    public function __construct()   {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('importText', TextareaType::class, ['label' => 'Glossary Text', 'required' => false])
            ->add('save', SubmitType::class, ['label' => 'Import Glossary Items']);
    }

    public function getName() : string
    {
        return 'paustianbookmodule_importgloss';
    }
}
