<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\CacheActionMenu;

use CPSIT\CpsMyraCloud\AdapterProvider\ExternalCacheProvider;
use CPSIT\CpsMyraCloud\Domain\Enum\Typo3CacheType;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Toolbar\ClearCacheActionsHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExternalClearCacheMenuItemProvider implements ClearCacheActionsHookInterface
{
    /**
     * @param array $cacheActions
     * @param array $optionValues
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    public function manipulateCacheActions(&$cacheActions, &$optionValues)
    {
        $provider = ExternalCacheProvider::getDefaultProviderItem();
        if ($provider && $provider->canExecute()) {
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $cacheActions[] = [
                'id' => $provider->getCacheId(),
                'title' => $provider->getCacheTitle(),
                'description' => $provider->getCacheDescription(),
                'href' => (string)$uriBuilder->buildUriFromRoute('ajax_external_cache_clear', ['clearAll' => true, 'type' => Typo3CacheType::PAGE, 'id' => -1]),
                'iconIdentifier' => $provider->getCacheIconIdentifier()
            ];
            $optionValues[] = $provider->getCacheId();
        }
    }
}