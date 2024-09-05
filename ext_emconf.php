<?php

/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => 'CpsMyraCloud',
    'description' => 'Clear-Cache for MyraCloud systems',
    'category' => 'be',
    'author' => 'Benjamin Rannow',
    'author_email' => 'b.rannow@familie-redlich.de',
    'author_company' => 'Familie-Redlich Digital GmbH',
    'state' => 'alpha',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.2',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];