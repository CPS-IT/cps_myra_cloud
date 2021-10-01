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
        $backendUser = $GLOBALS['BE_USER']??null;
        if ($backendUser && !$backendUser->isAdmin()) {
            return;
        }

        $this->setClearAllCacheButton($cacheActions, $optionValues);
        $this->setClearAllResourcesCacheButton($cacheActions, $optionValues);
    }

    /**
     * @param array $cacheActions
     * @param array $optionValues
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    public function setClearAllCacheButton(array &$cacheActions, array &$optionValues): void
    {
        $provider = ExternalCacheProvider::getDefaultProviderItem();
        if ($provider && $provider->canExecute()) {
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $cacheActions[] = [
                'id' => $provider->getCacheId(),
                'title' => $provider->getCacheTitle(),
                'description' => $provider->getCacheDescription(),
                'href' => (string)$uriBuilder->buildUriFromRoute('ajax_external_cache_clear', ['type' => Typo3CacheType::ALL_PAGE, 'id' => '-1']),
                'iconIdentifier' => $provider->getCacheIconIdentifier()
            ];
            $optionValues[] = $provider->getCacheId();
        }
    }

    /**
     * @param array $cacheActions
     * @param array $optionValues
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    public function setClearAllResourcesCacheButton(array &$cacheActions, array &$optionValues): void
    {
        $provider = ExternalCacheProvider::getDefaultProviderItem();
        if ($provider && $provider->canExecute()) {
            $id = $provider->getCacheId().'_resources';
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $cacheActions[] = [
                'id' => $id,
                'title' => $provider->getCacheTitle().'.resource',
                'description' => $provider->getCacheDescription().'.resource',
                'href' => (string)$uriBuilder->buildUriFromRoute('ajax_external_cache_clear', ['type' => Typo3CacheType::ALL_RESOURCES, 'id' => '-1']),
                'iconIdentifier' => $provider->getCacheIconIdentifier()
            ];
            $optionValues[] = $id;
        }
    }
}