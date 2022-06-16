<?php

declare(strict_types=1);
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of ExamForm
 * Set up the elements for a Exam form.
 *
 * @author paustian
 *
 */


class ConfigType extends AbstractType {


    /**
     * BlockType constructor.
     */
    public function __construct()   {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('summarize',CheckboxType::class, ['label' => 'Allow summarization feature', 'required' => true])
            ->add('sumlevel', RangeType::class,[
                    'label' => 'Max level of summarization (1-5, with 1 being less summarization)',
                    'attr' => [
                        'min' => 1,
                        'max' => 5
                    ]] )
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn-success'
                ]]);
    }

    public function getBlockPrefix() :string
    {
        return 'paustianbookmodule_modvars';
    }
}