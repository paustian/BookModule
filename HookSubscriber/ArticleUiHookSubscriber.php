<?php

declare(strict_types=1);

namespace Paustian\BookModule\HookSubscriber;

use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArticleUiHookSubscriber implements HookSubscriberInterface
{
    const ARTICLE_DISPLAY = 'book.ui_hooks.article.display_view';
    const ARTICLE_PROCESS = 'book.ui_hooks.article.process_edit';
    const ARTICLE_DELETE_PROCESS = 'book.ui_hooks.article.process_delete';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
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
        return UiHooksCategory::NAME;
    }

    public function getTitle() : string
    {
        return $this->translator->trans('Article attachment hooks');
    }

    public function getEvents() : array
    {
        return [
            UiHooksCategory::TYPE_DISPLAY_VIEW => self::ARTICLE_DISPLAY,
            UiHooksCategory::TYPE_PROCESS_EDIT => self::ARTICLE_PROCESS,
            UiHooksCategory::TYPE_PROCESS_DELETE => self::ARTICLE_DELETE_PROCESS,
        ];
    }

    public function getAreaName(): string
    {
        return 'subscriber.book.ui_hooks.article';
    }
}