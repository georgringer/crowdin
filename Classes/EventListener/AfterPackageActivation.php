<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin\EventListener;

use FriendsOfTYPO3\Crowdin\Setup;
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
