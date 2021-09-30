<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\ContextMenu;

use BR\Toolkit\Typo3\VersionWrapper\InstanceUtility;
use CPSIT\CpsMyraCloud\AdapterProvider\ExternalCacheProvider;
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
        if ($this->table !== 'pages')
            return false;

        $provider = ExternalCacheProvider::getDefaultProviderItem();
        if ($provider === null || !$provider->canExecute())
            return false;

        $page = $this->pageService->getPage((int)$this->identifier);
        return ($page !== null);
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
                'callbackAction' => $provider->getRequireJsFunction()
            ]
        ];
    }

    protected function canRender(string $itemName, string $type): bool
    {
        if (in_array($itemName, $this->disabledItems, true)) {
            return false;
        }

        $provider = ExternalCacheProvider::getDefaultProviderItem();
        return ($itemName === $provider->getCacheId());
    }
}