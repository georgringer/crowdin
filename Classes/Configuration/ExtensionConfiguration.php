<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\Configuration;

use
    TYPO3\CMS\Core\Configuration\ExtensionConfiguration as ExtensionConfigurationService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
class ExtensionConfiguration implements SingletonInterface
{

    /** @var string */
    protected $inlineTranslationProjectIdentifier = 'typo3-cms';

    /** @var bool */
    protected $useNewTranslationServer = false;

    public function __construct()
    {
        try {
            $settings = GeneralUtility::makeInstance(ExtensionConfigurationService::class)->get('crowdin');

            if ($settings['inlineTranslationProjectIdentifier']) {
                $this->inlineTranslationProjectIdentifier = (string)$settings['inlineTranslationProjectIdentifier'];
            }

            $this->useNewTranslationServer = (bool)$settings['useNewTranslationServer'];
        } catch (\Exception $e) {
            // do nothing
        }
    }

    /**
     * @return string
     */
    public function getInlineTranslationProjectIdentifier(): string
    {
        return $this->inlineTranslationProjectIdentifier;
    }

    /**
     * @return bool
     */
    public function useNewTranslationServer(): bool
    {
        return $this->useNewTranslationServer;
    }
}
