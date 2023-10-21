<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Crowdin',
    'description' => 'In-Context localization of XLF files handled by crowdin directly in the backend',
    'category' => 'be',
    'author' => 'Georg Ringer',
    'author_email' => '',
    'state' => 'beta',
    'version' => '3.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
