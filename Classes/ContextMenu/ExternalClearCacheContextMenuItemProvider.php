<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\ContextMenu;

use BR\Toolkit\Typo3\VersionWrapper\InstanceUtility;
use CPSIT\CpsMyraCloud\AdapterProvider\ExternalCacheProvider;
use CPSIT\CpsMyraCloud\Domain\Enum\Typo3CacheType;
use CPSIT\CpsMyraCloud\Service\PageService;
use TYPO3\CMS\Backend\ContextMenu\ItemProviders\AbstractProvider;

class ExternalClearCacheContextMenuItemProvider extends AbstractProvider
{
    private PageService $pageService;

    /**
     * @param string $table
     * @param string $identifier
     * @param string $context
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     */
    public function __construct(string $table, string $identifier, string $context)
    {
        $this->pageService = InstanceUtility::get(PageService::class);
        parent::__construct($table, $identifier, $context);
    }

    /**
     * @return bool
     */
    public function canHandle(): bool
    {
        $type = $this->getCacheType();
        if ($type <= Typo3CacheType::UNKNOWN)
            return false;

        $provider = ExternalCacheProvider::getDefaultProviderItem();
        if ($provider === null || !$provider->canInteract())
            return false;

        if ($type === Typo3CacheType::PAGE) {
            $page = $this->pageService->getPage((int)$this->getIdentifier());
            return ($page !== null);
        } elseif ($type === Typo3CacheType::RESOURCE) {
            return !empty($this->getIdentifier());
        }

        return false;
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
            if (strpos($id, '1:/') === 0) {
                return substr($id, 2);
            } else {
                return '/';
            }
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
        $provider = ExternalCacheProvider::getDefaultProviderItem();
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
        $provider = ExternalCacheProvider::getDefaultProviderItem();
        return $this->itemsConfiguration = [
            $provider->getCacheId() => [
                'type' => 'item',
                'label' => $provider->getCacheTitle(),
                'iconIdentifier' => $provider->getCacheIconIdentifier(),
                'callbackAction' => 'ClearPageViaContextMenu'
            ]
        ];
    }

    /**
     * @return int
     */
    private function getCacheType(): int
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

        $provider = ExternalCacheProvider::getDefaultProviderItem();
        return ($itemName === $provider->getCacheId());
    }
}