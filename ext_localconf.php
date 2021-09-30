<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied');
}

call_user_func(function ($extKey) {

    $iconRegistry = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        TYPO3\CMS\Core\Imaging\IconRegistry::class
    );
    $iconRegistry->registerIcon(
        'cps-cache-myra',
        TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:'.$extKey.'/Resources/Public/Icons/myra.svg']
    );


}, 'cps_myra_cloud');
