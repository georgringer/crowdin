<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Xclass;

use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Service\LanguagePackService;

class XclassedLanguagePackService extends LanguagePackService {

    /**
     * @inheritDoc
     */
    public function updateMirrorBaseUrl(): string
    {
        if (GeneralUtility::makeInstance(Features::class)->isFeatureEnabled('crowdin.newTranslationServer')) {
            $this->registry->set('languagePacks', 'baseUrl', 'https://typo3.org/fileadmin/ter/');
        } else {
            parent::updateMirrorBaseUrl();
        }
    }
}
