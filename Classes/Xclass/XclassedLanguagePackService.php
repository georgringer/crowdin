<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Xclass;

use GeorgRinger\Crowdin\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Service\LanguagePackService;

class XclassedLanguagePackService extends LanguagePackService
{

    /**
     * @inheritDoc
     */
    public function updateMirrorBaseUrl(): string
    {
        if ($this->newLanguageServerIsEnabled()) {
            $this->registry->set('languagePacks', 'baseUrl', 'https://typo3.org/fileadmin/ter/');
            return 'https://typo3.org/fileadmin/ter/';
        } else {
            return parent::updateMirrorBaseUrl();
        }
    }

    protected function newLanguageServerIsEnabled(): bool
    {
        return GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->useNewTranslationServer();
    }
}
