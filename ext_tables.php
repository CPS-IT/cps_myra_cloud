<?php

if (TYPO3_MODE == 'BE') {
    call_user_func(function ($extKey) {

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][$extKey] = Fr\MyraCloud\Hook\Typo3CacheManager::class . '->clearClear';

    }, 'fr_myra_cloud');
}