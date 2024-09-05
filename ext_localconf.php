<?php

defined('TYPO3') or die();

(function ($extKey) {
    $iconRegistry = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        TYPO3\CMS\Core\Imaging\IconRegistry::class
    );
    $iconRegistry->registerIcon(
        'cps-cache-myra',
        TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:'.$extKey.'/Resources/Public/Icons/myra.svg']
    );
})('cps_myra_cloud');
