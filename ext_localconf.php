<?php
defined('TYPO3_MODE') or die();

$boot = function () {

    if (TYPO3_MODE === 'BE') {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postProcess']['crowdin-inline-translation']
            = \GeorgRinger\Crowdin\Hooks\PageRendererHook::class . '->run';
    }

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Install\Service\LanguagePackService::class] = [
        'className' => \GeorgRinger\Crowdin\Xclass\XclassedLanguagePackService::class,
    ];
};

$boot();
unset($boot);
