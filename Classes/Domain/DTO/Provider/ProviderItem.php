<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Domain\DTO\Provider;

use Fr\MyraCloud\Domain\DTO\Adapter\AdapterRegisterInterface;

class ProviderItem implements ProviderItemRegisterInterface
{
    private AdapterRegisterInterface $adapter;

    /**
     * @param AdapterRegisterInterface $adapter
     */
    public function __construct(AdapterRegisterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getAdapter(): AdapterRegisterInterface
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

    public function getRequireJsCall(int $id, string $table = 'pages'): string
    {
        return 'require(["'. $this->getRequireJsNamespace() .'"],function(c){c.'. $this->getRequireJsFunction() .'(\''. $table .'\', ' . $id . ');});return false;';
    }

    public function getTypo3CssClass(): string
    {
        return 't3js-clear-page-cache';
    }
}