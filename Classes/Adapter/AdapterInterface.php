<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Adapter;

interface AdapterInterface extends AdapterRegisterInterface
{
    public function canExecute(): bool;
}