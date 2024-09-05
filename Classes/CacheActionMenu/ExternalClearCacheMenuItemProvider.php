<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\CacheActionMenu;

use CPSIT\CpsMyraCloud\AdapterProvider\AdapterProvider;
use CPSIT\CpsMyraCloud\Domain\Enum\Typo3CacheType;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;

readonly class ExternalClearCacheMenuItemProvider
{
    public function __construct(
        private AdapterProvider $provider,
        private UriBuilder $uriBuilder
    )
    {}

    /**
     * @param $cacheActions
     * @param $optionValues
     * @return void
     * @throws RouteNotFoundException
     */
    public function manipulateCacheActions(&$cacheActions, &$optionValues): void
    {
        $this->setClearAllCacheButton($cacheActions, $optionValues);
        $this->setClearAllResourcesCacheButton($cacheActions, $optionValues);
    }

    /**
     * @param array $cacheActions
     * @param array $optionValues
     * @return void
     * @throws RouteNotFoundException
     */
    public function setClearAllCacheButton(array &$cacheActions, array &$optionValues): void
    {
        $provider = $this->provider->getDefaultProviderItem();
        if ($provider && $provider->canInteract()) {
            $cacheActions[] = [
                'id' => $provider->getCacheId(),
                'title' => $provider->getCacheTitle(),
                'description' => $provider->getCacheDescription(),
                'href' => (string)$this->uriBuilder->buildUriFromRoute('ajax_external_cache_clear', ['type' => Typo3CacheType::ALL_PAGE->value, 'id' => '-1']),
                'iconIdentifier' => $provider->getCacheIconIdentifier()
            ];
            $optionValues[] = $provider->getCacheId();
        }
    }

    /**
     * @param array $cacheActions
     * @param array $optionValues
     * @throws RouteNotFoundException
     */
    public function setClearAllResourcesCacheButton(array &$cacheActions, array &$optionValues): void
    {
        $provider = $this->provider->getDefaultProviderItem();
        if ($provider && $provider->canInteract()) {
            $id = $provider->getCacheId().'_resources';
            $cacheActions[] = [
                'id' => $id,
                'title' => $provider->getCacheTitle().'.resource',
                'description' => $provider->getCacheDescription().'.resource',
                'href' => (string)$this->uriBuilder->buildUriFromRoute('ajax_external_cache_clear', ['type' => Typo3CacheType::ALL_RESOURCES->value, 'id' => '-1']),
                'iconIdentifier' => $provider->getCacheIconIdentifier()
            ];
            $optionValues[] = $id;
        }
    }
}
