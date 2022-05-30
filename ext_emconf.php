<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'netlogix sentry',
    'description' => '',
    'category' => 'plugin',
    'version' => '1.0.0',
    'state' => 'stable',
    'clearcacheonload' => 1,
    'author' => '',
    'author_email' => '',
    'constraints' => [
        'depends' => [
            'php' => '7.4.0-8.1.0',
            'typo3' => '10.4.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ]
];
