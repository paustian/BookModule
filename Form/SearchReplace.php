<?php
namespace Paustian\BookModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Zikula\Common\Translator\TranslatorInterface;
/**
 * Set up the elements for a SearchReplace form.
 *
 * @author paustian
 * 
 */
class SearchReplace extends AbstractType {
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
        $builder->add('searchText', TextType::class, ['label' => $this->translator->__('Search Text'), 'required' => true]);
        $builder->add('replaceText', TextType::class, ['label' => $this->translator->__('Replace Text'), 'required' => true]);
        $builder->add('preview', CheckboxType::class, ['label' => $this->translator->__('Show a preview of the Replace'), 'required' => false]);
        $builder->add('search', SubmitType::class, array('label' => $this->translator->__('Search')));
        
    }

    public function getName()
    {
        return 'paustianbookmodule_searchreplace';
    }
}

