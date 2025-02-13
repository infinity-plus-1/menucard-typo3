<?php

declare(strict_types=1);

namespace DS\CbBuilder\EventListener;

use TYPO3\CMS\Backend\Controller\Event\AfterBackendPageRenderEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent;
use TYPO3\CMS\Core\Configuration\Event\BeforeTcaOverridesEvent;
use TYPO3\CMS\Core\Utility\DebugUtility;

#[AsEventListener(
    identifier: 'DS\CbBuilder/before-tca-overrides'
)]
final readonly class TestEventListener
{
    public function __invoke(AfterTcaCompilationEvent $event): void
    {
        // $tca = $event->getTca();
        // $tca['tt_content']['test123'] = 'test3';
        // $event->setTca($tca);
        // file_put_contents(
        //     __DIR__ . '/../../../../var/log/my_debug.log',
        //     print_r($event, true) . PHP_EOL,
        //     FILE_APPEND
        // );
    }
}