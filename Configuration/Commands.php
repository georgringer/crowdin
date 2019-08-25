<?php
/**
 * Commands to be executed by typo3, where the key of the array
 * is the name of the command (to be called as the first argument after typo3).
 * Required parameter is the "class" of the command which needs to be a subclass
 * of Symfony/Console/Command.
 */

return [
    'crowdin:setApiCredentials' => [
        'class' => \GeorgRinger\Crowdin\Command\SetApiCredentialsCommand::class,
        'schedulable' => false,
    ],
    'crowdin:extractCoreTranslations' => [
        'class' => \GeorgRinger\Crowdin\Command\ExtractCoreTranslationsCommand::class,
        'schedulable' => true,
    ],
    'crowdin:extractExtTranslations' => [
        'class' => \GeorgRinger\Crowdin\Command\ExtractExtTranslationsCommand::class,
        'schedulable' => true,
    ],
    'crowdin:download' => [
        'class' => \GeorgRinger\Crowdin\Command\DownloadCommand::class,
        'schedulable' => false,
    ],
    'crowdin:export' => [
        'class' => \GeorgRinger\Crowdin\Command\ExportCommand::class,
        'schedulable' => false,
    ],
];
