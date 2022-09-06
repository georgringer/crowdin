<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\EventListener;

use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Package\Event\AfterPackageDeactivationEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AfterPackageDeactivation
{
    public function __invoke(AfterPackageDeactivationEvent $event)
    {
        if ($event->getPackageKey() !== 'crowdin') {
            return;
        }

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $localConfiguration = $configurationManager->getLocalConfiguration();
        $changesToBeWritten = false;
        if (isset($localConfiguration['SYS']['localization']['locales']['user']['t3'])) {
            unset($localConfiguration['SYS']['localization']['locales']['user']['t3']);
            $changesToBeWritten = true;
        }
        if (in_array('GeorgRinger\\Crowdin\\ViewHelpers\\Override', $localConfiguration['SYS']['fluid']['namespaces']['f'] ?? [], true)) {
            foreach ($localConfiguration['SYS']['fluid']['namespaces']['f'] as $k => $v) {
                if ($v === 'GeorgRinger\\Crowdin\\ViewHelpers\\Override') {
                    unset($localConfiguration['SYS']['fluid']['namespaces']['f'][$k]);
                    $changesToBeWritten = true;
                }
            }
            if (count($localConfiguration['SYS']['fluid']['namespaces']['f'] ?? []) === 2) {
                unset($localConfiguration['SYS']['fluid']['namespaces']['f']);
            }
        }
        if ($changesToBeWritten) {
            $configurationManager->writeLocalConfiguration($localConfiguration);
        }
    }
}
