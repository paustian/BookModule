<?php
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;
/**
 * Description of Chapter
 * Set up the elements for a Chapter form.
 *
 * @author paustian
 * 
 */
class Chapter extends AbstractType {
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
        $builder->add('name', TextType::class, ['label' => $this->translator->__('Chapter Name'), 'required' => true]);
        $builder->add('number', NumberType::class, ['label' => $this->translator->__('Chapter Number'), 'required' => true]);
        $builder->add('add', SubmitType::class, array('label' => $this->translator->__('Edit Chapter')));
        
    }

    public function getBlockPrefix()
    {
        return 'paustianbookmodule_chapter';
    }

    /**
     * OptionsResolverInterface is @deprecated and is supposed to be replaced by
     * OptionsResolver but docs not clear on implementation
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paustian\BookModule\Entity\BookChaptersEntity',
            'translator' => null,
        ));
    }
}

