<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Hooks;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class PageRendererHook
{

    private const LANGUAGE_KEY = 'kdh';

    public function run(array &$params)
    {
        if ($this->getBackendUser()->uc['lang'] === self::LANGUAGE_KEY) {
            $crowdinCode = '
                <script type="text/javascript">
                      var _jipt = [];
                      _jipt.push(["project", "typo3-cms"]);
                </script>
                <script type="text/javascript" src="//cdn.crowdin.com/jipt/jipt.js"></script>';

            $params['jsLibs'] = $crowdinCode . $params['jsLibs'];
        }
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
