<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Domain\DTO\Adapter;

interface AdapterRegisterInterface
{
    public function getCacheId(): string;

    public function getCacheIconIdentifier(): string;

    public function getCacheTitle(): string;

    public function getCacheDescription(): string;

    public function getRequireJsNamespace(): string;

    public function getRequireJsFunction(): string;
}