<?php

$GLOBALS['SiteConfiguration']['site']['columns']['myra_host'] = [
    'label' => 'LLL:EXT:fr_myra_cloud/Resources/Private/Language/locallang_myra.xlf:tca.site.cache.identifier',
    'config' => [
        'type' => 'input',
        'default' => '',
        'placeholder' => '',
        'size' => 255
    ]
];

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] .= ',--div--;MyraCloud,myra_host';
