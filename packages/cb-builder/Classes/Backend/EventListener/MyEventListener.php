<?php

declare(strict_types=1);

namespace DS\CbBuilder\Backend\EventListener;

use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;

#[AsEventListener(
    identifier: 'my-extension/preview-rendering-example-ctype',
)]
final readonly class MyEventListener
{
    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {

    }
}