<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin\EventListener;

use FriendsOfTYPO3\Crowdin\Setup;
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
