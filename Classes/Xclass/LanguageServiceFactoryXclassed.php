<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Xclass;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Localization\Locale;

class LanguageServiceFactoryXclassed extends LanguageServiceFactory
{
     /**
     * Factory method to create a language service object.
     *
     * @param Locale|string $locale the locale
     */
    public function create(Locale|string $locale): LanguageService
    {
        $obj = GeneralUtility::makeInstance(LanguageServiceXclassed::class, $this->locales, $this->localizationFactory, $this->runtimeCache);
        $obj->init($locale);

        return $obj;
    }
}
