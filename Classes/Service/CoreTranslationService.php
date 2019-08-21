<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use TYPO3\CMS\Core\Core\Environment;

class CoreTranslationService extends AbstractTranslationServerService
{


    public function getTranslation(string $key, string $language, int $version)
    {
        $url = $this->getCoreExtensionUrl($key, $language, $version);
        $filePath = $this->downloadPackage($url, $key, $language, $version);
        $absoluteLanguagePath = Environment::getVarPath() . '/transient/crowdin/v' . $version . '-' . $key . '-l10n-' . $language . '/';


        $this->unzip($filePath, $absoluteLanguagePath);
        $this->processFiles($absoluteLanguagePath);
        $this->upload($absoluteLanguagePath, $language, true);
    }

    protected function getCoreExtensionUrl(string $key, string $language, int $version)
    {
        return sprintf('https://typo3.org/fileadmin/ter/%s/%s/%s-l10n/%s-l10n-%s.v%s.zip',
            $key{0},
            $key{1},
            $key,
            $key,
            $language,
            $version
        );
    }

}
