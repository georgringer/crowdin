<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Xclass;

use GeorgRinger\Crowdin\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Service\LanguagePackService;

class XclassedLanguagePackService extends LanguagePackService
{
    private const URL = 'https://media.githubusercontent.com/media/georgringer/crowdin-files/master/fileadmin/ter/';

    /**
     * @inheritDoc
     */
    public function updateMirrorBaseUrl(): string
    {
        if ($this->newLanguageServerIsEnabled()) {
            $this->registry->set('languagePacks', 'baseUrl', self::URL);
            return self::URL;
        } else {
            return parent::updateMirrorBaseUrl();
        }
    }

    protected function newLanguageServerIsEnabled(): bool
    {
        return false;
        return GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->useNewTranslationServer();
    }
}
