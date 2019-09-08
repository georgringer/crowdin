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
    'crowdin:switchApiCredentials' => [
        'class' => \GeorgRinger\Crowdin\Command\SwitchApiCredentialsCommand::class,
        'schedulable' => false,
    ],
    'crowdin:downloadPootleCoreTranslation' => [
        'class' => \GeorgRinger\Crowdin\Command\DownloadPootleCoreTranslationsCommand::class,
        'schedulable' => true,
    ],
    'crowdin:downloadPootleExtTranslation' => [
        'class' => \GeorgRinger\Crowdin\Command\DownloadPootleExtTranslationsCommand::class,
        'schedulable' => true,
    ],
    'crowdin:downloadCrowdinTranslations' => [
        'class' => \GeorgRinger\Crowdin\Command\DownloadCrowdinTranslationsCommand::class,
        'schedulable' => false,
    ],
    'crowdin:export' => [
        'class' => \GeorgRinger\Crowdin\Command\ExportCommand::class,
        'schedulable' => true,
    ],
    'crowdin:clean' => [
        'class' => \GeorgRinger\Crowdin\Command\CleanCommand::class,
        'schedulable' => false,
    ],
    'crowdin:status' => [
        'class' => \GeorgRinger\Crowdin\Command\StatusCommand::class,
        'schedulable' => false,
    ],
];
