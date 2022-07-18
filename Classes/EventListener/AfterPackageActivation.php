<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\EventListener;

use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Package\Event\AfterPackageActivationEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AfterPackageActivation
{

    public function __invoke(AfterPackageActivationEvent $event)
    {
        if ($event->getPackageKey() === 'crowdin') {
            $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
            $localConfiguration = $configurationManager->getLocalConfiguration();

            $changesToBeWritten = false;
            if (!isset($localConfiguration['SYS']['localization']['locales']['user']['t3'])) {
                $localConfiguration['SYS']['localization']['locales']['user']['t3'] = 'Crowdin In-Context Localization';
                $changesToBeWritten = true;
            }

            if (!in_array('GeorgRinger\\Crowdin\\ViewHelpers\\Override', $localConfiguration['SYS']['fluid']['namespaces']['f'] ?? [], true)) {
                if (!in_array('TYPO3\\CMS\\Fluid\\ViewHelpers', $localConfiguration['SYS']['fluid']['namespaces']['f'] ?? [], true)) {
                    $localConfiguration['SYS']['fluid']['namespaces']['f'][] = 'TYPO3\\CMS\\Fluid\\ViewHelpers';
                }
                if (!in_array('TYPO3Fluid\\Fluid\\ViewHelpers', $localConfiguration['SYS']['fluid']['namespaces']['f'] ?? [], true)) {
                    $localConfiguration['SYS']['fluid']['namespaces']['f'][] = 'TYPO3Fluid\\Fluid\\ViewHelpers';
                }
                $localConfiguration['SYS']['fluid']['namespaces']['f'][] = 'GeorgRinger\\Crowdin\\ViewHelpers\\Override';
                $changesToBeWritten = true;
            }

            if ($changesToBeWritten) {
                $configurationManager->writeLocalConfiguration($localConfiguration);
            }
        }
    }

}
