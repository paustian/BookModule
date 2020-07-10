<?php

declare(strict_types=1);

/**
 * News.
 *
 * @copyright Timothy Paustian
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Timothy Paustian
 *
 */

namespace Paustian\BookModule\HookSubscriber;

use Zikula\Bundle\HookBundle\Category\FormAwareCategory;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Base class for form aware hook subscriber.
 */
class FormAwareHookSubscriber implements HookSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getOwner() : string
    {
        return 'PaustianBookModule';
    }

    public function getCategory() : string
    {
        return FormAwareCategory::NAME;
    }

    public function getTitle() : string
    {
        return $this->translator->trans('Message form aware subscriber');
    }

    public function getEvents() : array
    {
        return [
            // Display hook for create/edit forms.
            FormAwareCategory::TYPE_EDIT => 'book.form_aware_hook.article.edit',
            // Process the results of the edit form after the main form is processed.
            FormAwareCategory::TYPE_PROCESS_EDIT => 'book.form_aware_hook.article.process_edit',
            // Display hook for delete forms.
            FormAwareCategory::TYPE_DELETE => 'book.form_aware_hook.messages.delete',
            // Process the results of the delete form after the main form is processed.
            FormAwareCategory::TYPE_PROCESS_DELETE => 'book.form_aware_hook.messages.process_delete'
        ];
    }

    public function getAreaName(): string
    {
        return 'subscriber.book.form_aware_hook.messages';
    }
}
