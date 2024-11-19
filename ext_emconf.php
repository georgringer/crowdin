<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Crowdin',
    'description' => 'In-Context localization of XLF files handled by crowdin directly in the backend',
    'category' => 'be',
    'author' => 'Georg Ringer',
    'author_email' => '',
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'version' => '2.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
