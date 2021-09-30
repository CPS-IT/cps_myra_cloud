<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Adapter;

use Fr\MyraCloud\Domain\DTO\Typo3\PageSlugInterface;
use Fr\MyraCloud\Domain\DTO\Typo3\SiteConfigExternalIdentifierInterface;
use Fr\MyraCloud\Domain\DTO\Typo3\SiteConfigInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Myracloud\WebApi\Endpoint\AbstractEndpoint;
use Myracloud\WebApi\Endpoint\CacheClear;
use Myracloud\WebApi\Endpoint\DnsRecord;
use Myracloud\WebApi\Middleware\Signature;
use Psr\Http\Message\RequestInterface;

class MyraApiAdapter extends BaseAdapter
{
    protected array $clients;

    private const CONFIG_NAME_API_KEY = 'myra_api_key';
    private const CONFIG_NAME_ENDPOINT = 'myra_endpoint';
    private const CONFIG_NAME_SECRET = 'myra_secret';

    public function getCacheId(): string
    {
        return 'fr_myra_cloud';
    }

    public function getCacheIconIdentifier(): string
    {
        return 'fr-cache-myra';
    }

    public function getCacheTitle(): string
    {
        return 'LLL:EXT:fr_myra_cloud/Resources/Private/Language/locallang_myra.xlf:title';
    }

    public function getCacheDescription(): string
    {
        return 'LLL:EXT:fr_myra_cloud/Resources/Private/Language/locallang_myra.xlf:description';
    }

    protected function getAdapterConfigPrefix(): string
    {
        return 'myra';
    }

    /**
     * @param SiteConfigInterface $site
     * @return bool
     */
    public function clearSiteCache(SiteConfigInterface $site): bool
    {
        if (!$this->canExecute()) {
            return false;
        }

        $r = false;
        foreach ($this->getFqdnForSite($site) as $domain) {
            $r |= $this->clearCacheDomain($site->getExternalIdentifier(), $domain, '/', true);
        }

        return (bool)$r;
    }

    /**
     * @param SiteConfigInterface $site
     * @param PageSlugInterface $pageSlug
     * @return bool
     */
    public function clearPageCache(SiteConfigInterface $site, PageSlugInterface $pageSlug): bool
    {
        if (!$this->canExecute()) {
            return false;
        }

        $r = false;
        foreach ($this->getFqdnForSite($site) as $domain) {
            $r |= $this->clearCacheDomain($site->getExternalIdentifier(), $domain, $pageSlug->getSlug());
        }

        return (bool)$r;
    }

    /**
     * @param string $domain
     * @param string $fqdn
     * @param string $path
     * @param bool $recursive
     * @return bool
     */
    private function clearCacheDomain(string $domain, string $fqdn, string $path = '/', bool $recursive = false): bool
    {
        try {
            $r = $this->getCacheClearApi()->clear($domain, $fqdn, $path, $recursive);
        } catch (GuzzleException $e) {
            return false;
        }

        return (!empty($r) && ($r['error']??true) === false);
    }

    /**
     * @param SiteConfigExternalIdentifierInterface $site
     * @throws \BR\Toolkit\Exceptions\CacheException
     * @return string[]
     */
    private function getFqdnForSite(SiteConfigExternalIdentifierInterface $site): array
    {
        return $this->cacheService->cache(
            'myra_getFqdnForSite_' . $site->getExternalIdentifier(),
            function () use ($site) {
                $r = $this->getDomainRecordsForDomain($site->getExternalIdentifier());
                $fqdn = [];
                if (!empty($r) && $r['error'] === false) {
                    foreach ($r['list'] as $recordItem) {
                        $name = $recordItem['name']??'';
                        $active = (bool)($recordItem['active']??false);
                        $enable = (bool)($recordItem['enabled']??false);
                        if ($active && $enable && $name !== '') {
                            $fqdn[crc32($name)] = $name;
                        }
                    }
                }

                return array_values($fqdn);
            },
            'MYRA_CLOUD',
            680000
        );
    }

    /**
     * @param string $domain
     * @return array
     */
    private function getDomainRecordsForDomain(string $domain): array
    {
        /** @var DnsRecord $st */
        $st = $this->getEndPointApi(DnsRecord::class);
        $r = [];
        try {
            $r = $st->getList($domain);
        } catch (\Exception $e) {
        }

        return $r;
    }

    /**
     * @return CacheClear|null
     */
    private function getCacheClearApi(): ?CacheClear
    {
        /** @var ?CacheClear $instance */
        $instance = $this->getEndPointApi(CacheClear::class);
        return $instance;
    }

    /**
     * @param string $className
     * @return AbstractEndpoint|null
     */
    private function getEndPointApi(string $className): ?AbstractEndpoint
    {
        if (!class_exists($className)) {
            return null;
        }

        $client = $this->getMyraClient();
        try {
            return $client !== null ? new $className($client) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return ClientInterface|null
     */
    private function getMyraClient(): ?ClientInterface
    {
        $config = $this->getAdapterConfig();
        $instanceId = md5(serialize($config));
        if (isset($this->clients[$instanceId])) {
            return $this->clients[$instanceId];
        }

        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());

        $signature = new Signature($config[self::CONFIG_NAME_SECRET], $config[self::CONFIG_NAME_API_KEY]);
        $stack->push(
            Middleware::mapRequest(
                function (RequestInterface $request) use ($signature) {
                    return $signature->signRequest($request);
                }
            )
        );
        return $this->clients[$instanceId] = new Client(
            [
                'base_uri' => 'https://' . $config[self::CONFIG_NAME_ENDPOINT] . '/' . 'en' . '/rapi',
                'handler'  => $stack,
            ],
        );
    }
}