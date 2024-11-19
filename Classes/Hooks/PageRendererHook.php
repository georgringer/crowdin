<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Hooks;

use GeorgRinger\Crowdin\ExtensionConfiguration;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\RequestId;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageRendererHook
{
    private const LANGUAGE_KEY = 't3';

    public function run(array &$params): void
    {
        if ($this->getBackendUser()->user['uid'] ?? 0 && ($this->getBackendUser()->user['lang'] ?? '') === self::LANGUAGE_KEY) {
            $projectIdentifier = $this->getProjectIdentifier();
            if ($projectIdentifier) {
                $out = [];
                if ((new Typo3Version())->getMajorVersion() >= 12) {
                    $nonce = GeneralUtility::getContainer()->get(RequestId::class)->nonce->consume();
                    $js = '
                        <script nonce="' . $nonce . '">
                            var _jipt = [];
                            _jipt.push(["project", ' . GeneralUtility::quoteJSvalue($projectIdentifier) . ']);
                        </script>
                        <script nonce="' . $nonce . '" src="https://cdn.crowdin.com/jipt/jipt.js"></script>';
                } else {
                    $js = '
                        <script type="text/javascript">
                              var _jipt = [];
                              _jipt.push(["project", ' . GeneralUtility::quoteJSvalue($projectIdentifier) . ']);
                        </script>
                        <script type="text/javascript" src="https://cdn.crowdin.com/jipt/jipt.js"></script>';
                }

                $params['jsLibs'] = $js . $params['jsLibs'];
            }
        }
    }

    protected function getProjectIdentifier(): string
    {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        if ($extensionConfiguration->isUsedForCore()) {
            return 'typo3-cms';
        }

        return $extensionConfiguration->getCrowdinIdentifier();
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
