<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Service;

use Fr\MyraCloud\Adapter\MyraApiAdapter;
use Fr\MyraCloud\Domain\DTO\CacheConfig;

class MyraCacheService
{
    private MyraApiAdapter $myraApiAdapter;

    /**
     * @param MyraApiAdapter $myraApiAdapter
     */
    public function __construct(MyraApiAdapter $myraApiAdapter)
    {
        $this->myraApiAdapter = $myraApiAdapter;
    }


    /**
     * @param CacheConfig $config
     */
    public function clearCacheWithConfig(CacheConfig $config): void
    {
        // TODO: get page domain or all domains
        // TODO: get from fqdn from page_uid (if all pages '/')

        //$this->myraApiAdapter->clearCache('','','');
    }
}