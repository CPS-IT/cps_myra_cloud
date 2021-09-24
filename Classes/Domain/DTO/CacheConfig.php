<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Domain\DTO;

use Fr\MyraCloud\Domain\Enum\CacheConfigCommandMapping;

class CacheConfig
{
    private int $type;

    private int $pid;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $cmd = $config['cacheCmd']??'-1';
        $this->type = CacheConfigCommandMapping::$mapping[$cmd]??CacheConfigCommandMapping::PAGE;
        $this->pid = (int)$cmd;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return (
            // all pages
            $this->type === CacheConfigCommandMapping::PAGES ||
            (
                // specific page
                $this->type === CacheConfigCommandMapping::PAGE &&
                $this->pid > 0
            )
        );
    }
}