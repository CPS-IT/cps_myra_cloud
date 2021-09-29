<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Adapter;

use Fr\MyraCloud\Domain\DTO\Adapter\AdapterRegisterInterface;
use TYPO3\CMS\Core\SingletonInterface;

abstract class BaseAdapter implements SingletonInterface, AdapterRegisterInterface
{
    public function getRequireJsNamespace(): string
    {
        return 'TYPO3/CMS/FrMyraCloud/ContextMenuActions';
    }

    public function getRequireJsFunction(): string
    {
        return 'ClearExternalCache';
    }

    abstract public function getCacheId(): string;

    abstract public function getCacheIconIdentifier(): string;

    abstract public function getCacheTitle(): string;

    abstract public function getCacheDescription(): string;
}