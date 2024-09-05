<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Domain\DTO\Provider;

use CPSIT\CpsMyraCloud\Adapter\AdapterInterface;
use CPSIT\CpsMyraCloud\Domain\Enum\Typo3CacheType;

class ProviderItem implements ProviderItemRegisterInterface
{
    private AdapterInterface $adapter;

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    public function getCacheId(): string
    {
        return $this->getAdapter()->getCacheId();
    }

    public function getCacheIconIdentifier(): string
    {
        return $this->getAdapter()->getCacheIconIdentifier();
    }

    public function getCacheTitle(): string
    {
        return $this->getAdapter()->getCacheTitle();
    }

    public function getCacheDescription(): string
    {
        return $this->getAdapter()->getCacheDescription();
    }

    public function getRequireJsNamespace(): string
    {
        return $this->getAdapter()->getRequireJsNamespace();
    }

    public function getRequireJsFunction(): string
    {
        return $this->getAdapter()->getRequireJsFunction();
    }

    public function getRequireJsCall(string $id, Typo3CacheType $type = Typo3CacheType::UNKNOWN): string
    {
        return 'require(["'. $this->getRequireJsNamespace() .'"],function(c){c.'. $this->getRequireJsFunction() .'('. $type->value .', \'' . $id . '\');});return false;';
    }

    public function getTypo3CssClass(): string
    {
        return 't3js-clear-page-cache';
    }

    public function canExecute(): bool
    {
        return $this->getAdapter()->canExecute();
    }

    public function canInteract(): bool
    {
        return $this->getAdapter()->canInteract();
    }

    public function canAutomated(): bool
    {
        return $this->getAdapter()->canAutomated();
    }
}
