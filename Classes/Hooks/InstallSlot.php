<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Hooks;


use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Utility\InstallUtility;

class InstallSlot
{
    /**
     * Setup EXT:crowdin after installing
     *
     * @param string $extensionKey
     * @param InstallUtility $installUtility
     */
    public function setupCrowdinAfterInstall(string $extensionKey, InstallUtility $installUtility)
    {
        if ($extensionKey !== 'crowdin') {
            return;
        }

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);

        $localConfiguration = $configurationManager->getLocalConfiguration();
        if (!isset($localConfiguration['SYS']['localization']['locales']['user']['t3'])) {
            $localConfiguration['SYS']['localization']['locales']['user']['t3'] = 'Crowdin In-Context Localization';
            $configurationManager->writeLocalConfiguration($localConfiguration);
        }
    }

    /**
     * Remove crowdin configuration
     *
     * @param string $extensionKey
     * @param InstallUtility $installUtility
     */
    public function removeCrowdinAfterInstall(string $extensionKey, InstallUtility $installUtility)
    {
        if ($extensionKey !== 'crowdin') {
            return;
        }

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);

        $localConfiguration = $configurationManager->getLocalConfiguration();
        if (isset($localConfiguration['SYS']['localization']['locales']['user']['t3'])) {
            unset($localConfiguration['SYS']['localization']['locales']['user']['t3']);
            $configurationManager->writeLocalConfiguration($localConfiguration);
        }
    }
}
