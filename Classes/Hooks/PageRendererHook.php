<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Hooks;

use GeorgRinger\Crowdin\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageRendererHook
{
    private const LANGUAGE_KEY = 't3';

    public function run(array &$params)
    {
        if ($this->getBackendUser()->uc['lang'] === self::LANGUAGE_KEY
            && $projectIdentifier = $this->getProjectIdentifier()) {
            $crowdinCode = '
                <script type="text/javascript">
                      var _jipt = [];
                      _jipt.push(["project", ' . GeneralUtility::quoteJSvalue($projectIdentifier) . ']);
                </script>
                <script type="text/javascript" src="//cdn.crowdin.com/jipt/jipt.js"></script>';

            $params['jsLibs'] = $crowdinCode . $params['jsLibs'];
        }
    }

    protected function getProjectIdentifier(): string
    {
        return GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->getInlineTranslationProjectIdentifier();
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
