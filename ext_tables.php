<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied');
}

if (TYPO3_MODE == 'BE') {
    call_user_func(function ($extKey) {

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Backend\Template\Components\ButtonBar']['getButtonsHook'][$extKey.'_clearCache'] = Fr\MyraCloud\ButtonBar\ExternalClearCacheButtonBarItemProvider::class . '->clearPageCache';
        $GLOBALS['TYPO3_CONF_VARS']['BE']['ContextMenu']['ItemProviders'][$extKey.'_clearCache'] = Fr\MyraCloud\ContextMenu\ExternalClearCacheContextMenuItemProvider::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['additionalBackendItems']['cacheActions'][$extKey.'_clearCache'] = Fr\MyraCloud\CacheActionMenu\ExternalClearCacheMenuItemProvider::class;
    }, 'fr_myra_cloud');
}