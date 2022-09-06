<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\EventListener;

use GeorgRinger\Crowdin\Setup;
use TYPO3\CMS\Core\Package\Event\AfterPackageDeactivationEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AfterPackageDeactivation
{
    public function __invoke(AfterPackageDeactivationEvent $event)
    {
        if ($event->getPackageKey() === 'crowdin') {
            GeneralUtility::makeInstance(Setup::class)->disable();
        }

    }
}
