<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\ContextMenu;

use CPSIT\CpsMyraCloud\AdapterProvider\AdapterProvider;
use CPSIT\CpsMyraCloud\Domain\Enum\Typo3CacheType;
use CPSIT\CpsMyraCloud\Service\PageService;
use TYPO3\CMS\Backend\ContextMenu\ItemProviders\AbstractProvider;

class ExternalClearCacheContextMenuItemProvider extends AbstractProvider
{
    public function __construct(
        private readonly AdapterProvider $adapterProvider,
        private readonly PageService $pageService
    )
    {
        parent::__construct();
    }

    /**
     * @return bool
     */
    public function canHandle(): bool
    {
        $canHandle = false;
        try {
            $type = $this->getCacheType();
            if ($type <= Typo3CacheType::UNKNOWN)
                return false;

            $provider = $this->adapterProvider->getDefaultProviderItem();
            if ($provider === null || !$provider->canInteract())
                return false;

            if ($type === Typo3CacheType::PAGE) {
                $page = $this->pageService->getPage((int)$this->getIdentifier());
                return $canHandle = ($page !== null);
            } elseif ($type === Typo3CacheType::RESOURCE) {
                return $canHandle = !empty($this->getIdentifier());
            }
        } finally {
            return $canHandle;
        }
    }

    /**
     * @return string
     */
    protected function getIdentifier(): string
    {
        $id = $this->identifier;
        $type = $this->getCacheType();
        if ($type === Typo3CacheType::PAGE) {
            if(!is_numeric($id))
                return '';

            return $id;
        } elseif ($type === Typo3CacheType::RESOURCE) {
            return $id;
        }

        return '';
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 10;
    }

    /**
     * @param string $itemName
     * @return string[]
     */
    protected function getAdditionalAttributes(string $itemName): array
    {
        $provider = $this->adapterProvider->getDefaultProviderItem();
        if ($provider) {
            return [
                'data-callback-module' => $provider->getRequireJsNamespace()
            ];
        }

        return [];
    }

    /**
     * @param array $items
     * @return array
     */
    public function addItems(array $items): array
    {
        $this->initDisabledItems();
        $localItems = $this->prepareItems($this->setupItem());
        return $items + $localItems;
    }

    /**
     * @return array[]
     */
    private function setupItem(): array
    {
        $provider = $this->adapterProvider->getDefaultProviderItem();
        return $this->itemsConfiguration = [
            $provider->getCacheId() => [
                'type' => 'item',
                'label' => $provider->getCacheTitle(),
                'iconIdentifier' => $provider->getCacheIconIdentifier(),
                'callbackAction' => 'ClearPageViaContextMenu'
            ]
        ];
    }

    private function getCacheType(): Typo3CacheType
    {
        if ($this->table === 'pages') {
            return Typo3CacheType::PAGE;
        } elseif (in_array($this->table, [
            'sys_file',
            'sys_file_storage'
        ])) {
            return Typo3CacheType::RESOURCE;
        }

        return Typo3CacheType::INVALID;
    }

    /**
     * @param string $itemName
     * @param string $type
     * @return bool
     */
    protected function canRender(string $itemName, string $type): bool
    {
        if (in_array($itemName, $this->disabledItems, true)) {
            return false;
        }

        $provider = $this->adapterProvider->getDefaultProviderItem();
        return ($itemName === $provider->getCacheId());
    }
}
