<?php

/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => 'MyraCloud',
    'description' => 'Clear-Cache for MyraCloud systems',
    'category' => 'be',
    'author' => 'Benjamin Rannow',
    'author_email' => 'b.rannow@familie-redlich.de',
    'author_company' => 'Familie-Redlich Digital GmbH',
    'state' => 'experimental',
    'clearCacheOnLoad' => 0,
    'version' => '0.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];