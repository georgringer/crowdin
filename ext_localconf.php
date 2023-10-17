<?php

defined('TYPO3') or exit();

$boot = static function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postProcess']['crowdin-inline-translation']
        = \GeorgRinger\Crowdin\Hooks\PageRendererHook::class.'->run';

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Localization\LanguageService::class] = [
        'className' => \GeorgRinger\Crowdin\Xclass\LanguageServiceXclassed::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Localization\LanguageServiceFactory::class] = [
        'className' => \GeorgRinger\Crowdin\Xclass\LanguageServiceFactoryXclassed::class,
    ];
};

$boot();
unset($boot);
