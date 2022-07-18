<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Xclass;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LanguageServiceFactoryXclassed extends LanguageServiceFactory
{

    /**
     * Factory method to create a language service object.
     *
     * @param string $locale the locale (= the TYPO3-internal locale given)
     * @return LanguageService
     */
    public function create(string $locale): LanguageService
    {
        $obj = GeneralUtility::makeInstance(LanguageServiceXclassed::class, $this->locales, $this->localizationFactory, $this->runtimeCache);
        $obj->init($locale);
        return $obj;
    }

}
