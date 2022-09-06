<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\EventListener;

use GeorgRinger\Crowdin\Setup;
use TYPO3\CMS\Core\Package\Event\AfterPackageActivationEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AfterPackageActivation
{
    public function __invoke(AfterPackageActivationEvent $event)
    {
        if ($event->getPackageKey() === 'crowdin') {
            GeneralUtility::makeInstance(Setup::class)->enable();
        }
    }
}
