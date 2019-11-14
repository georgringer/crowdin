<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Hooks;

use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
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

        $this->createLanguagePack();
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

    protected function createLanguagePack(): void
    {
        $l10nDir = Environment::getLabelsPath();
        $targetDir = $l10nDir . '/t3/';
        if (!is_dir($targetDir) || empty(GeneralUtility::get_dirs($targetDir))) {
            GeneralUtility::mkdir_deep($targetDir);

            $this->unzip(
                ExtensionManagementUtility::extPath('crowdin', 'Resources/Private/LanguageExport/t3.zip'),
                $l10nDir
            );
        }
    }

    protected function unzip(string $file, string $path)
    {
        $zip = new \ZipArchive();
        $res = $zip->open($file);
        if ($res === true) {
            $zip->extractTo($path);
            $zip->close();
        } else {
            throw new \RuntimeException(sprintf('Could not extract zip "%s"', $file), 1566421924);
        }
    }
}
