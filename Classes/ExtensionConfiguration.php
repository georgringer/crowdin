<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration as ExtensionConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
class ExtensionConfiguration
{
    protected string $crowdinIdentifier = '';
    protected string $extensionKey = '';
    protected bool $usedForCore = false;

    public function __construct()
    {
        $config = GeneralUtility::makeInstance(ConfigurationLoader::class);
        $config->get();

        try {
            $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfigurationService::class)->get('crowdin');
            if ($extensionConfiguration['core'] ?? false) {
                $this->usedForCore = true;
            } else {
                $crowdinConfiguration = GeneralUtility::makeInstance(ConfigurationLoader::class)->get();

                $extensionKey = (string) ($extensionConfiguration['extensionKey'] ?? '');
                if (!isset($crowdinConfiguration[$extensionKey])) {
                    // todo logging?
                } else {
                    $this->crowdinIdentifier = $crowdinConfiguration[$extensionKey];
                    $this->extensionKey = $extensionKey;
                }
            }
        } catch (ExtensionConfigurationExtensionNotConfiguredException $e) {
            // do nothing
        } catch (ExtensionConfigurationPathDoesNotExistException $e) {
            // do nothing
        }
    }

    public function getCrowdinIdentifier(): string
    {
        return $this->crowdinIdentifier;
    }

    public function getExtensionKey(): string
    {
        return $this->extensionKey;
    }

    public function isUsedForCore(): bool
    {
        return $this->usedForCore;
    }
}
