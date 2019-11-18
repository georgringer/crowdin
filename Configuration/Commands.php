<?php
/**
 * Commands to be executed by typo3, where the key of the array
 * is the name of the command (to be called as the first argument after typo3).
 * Required parameter is the "class" of the command which needs to be a subclass
 * of Symfony/Console/Command.
 */

return [
    'crowdin:convertXml2Xlf' => [
        'class' => \GeorgRinger\Crowdin\Command\ConvertXmlToXlfCommand::class,
        'schedulable' => false,
    ],
    'crowdin:addOriginalAttribute' => [
        'class' => \GeorgRinger\Crowdin\Command\Cleanup\OriginalAttributeCommand::class,
        'schedulable' => false,
    ],
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
    'crowdin:extractCore' => [
        'class' => \GeorgRinger\Crowdin\Command\CrowdinExtractCoreCommand::class,
        'schedulable' => true,
    ],
    'crowdin:extractExt' => [
        'class' => \GeorgRinger\Crowdin\Command\CrowdinExtractExtCommand::class,
        'schedulable' => true,
    ],
    'crowdin:build' => [
        'class' => \GeorgRinger\Crowdin\Command\BuildCommand::class,
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
