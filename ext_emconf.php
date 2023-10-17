<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Crowdin',
    'description' => 'In-Context localization of XLF files handled by crowdin directly in the backend',
    'category' => 'be',
    'author' => 'Georg Ringer',
    'author_email' => '',
    'state' => 'beta',
    'version' => '2.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
