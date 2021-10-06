<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Adapter;

use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\PageSlugInterface;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\SiteConfigExternalIdentifierInterface;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\SiteConfigInterface;
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
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\SysLog\Action\Cache as SystemLogCacheAction;
use TYPO3\CMS\Core\SysLog\Error as SystemLogErrorClassification;
use TYPO3\CMS\Core\SysLog\Type as SystemLogType;

class MyraApiAdapter extends BaseAdapter
{
    protected array $clients;
    private static array $multiClearCacheProtection = [];

    private const CONFIG_NAME_API_KEY = 'myra_api_key';
    private const CONFIG_NAME_ENDPOINT = 'myra_endpoint';
    private const CONFIG_NAME_SECRET = 'myra_secret';

    public function getCacheId(): string
    {
        return 'cps_myra_cloud';
    }

    public function getCacheIconIdentifier(): string
    {
        return 'cps-cache-myra';
    }

    public function getCacheTitle(): string
    {
        return 'LLL:EXT:cps_myra_cloud/Resources/Private/Language/locallang_myra.xlf:title';
    }

    public function getCacheDescription(): string
    {
        return 'LLL:EXT:cps_myra_cloud/Resources/Private/Language/locallang_myra.xlf:description';
    }

    protected function getAdapterConfigPrefix(): string
    {
        return 'myra';
    }

    /**
     * @param SiteConfigInterface $site
     * @param PageSlugInterface|null $pageSlug
     * @param bool $recursive
     * @return bool
     * @throws \BR\Toolkit\Exceptions\CacheException
     */
    public function clearCache(SiteConfigInterface $site, ?PageSlugInterface $pageSlug = null, bool $recursive = false): bool
    {
        if (!$this->canExecute()) {
            return false;
        }

        $r = false;
        // if no slug provided / clear root
        $slug = ($pageSlug !== null) ? $pageSlug->getSlug() : '/';
        foreach ($site->getExternalIdentifierList() as $domainIdentifier) {
            foreach ($this->getFqdnForSite($domainIdentifier) as $subDomain) {
                $r |= $this->clearCacheDomain($domainIdentifier, $subDomain, $slug, $recursive);
            }
        }

        return (bool)$r;
    }

    /**
     * @param string $siteRef
     * @param string $fqdn
     * @param string $path
     * @param bool $recursive
     * @return string
     */
    protected function getSendHash(string $siteRef, string $fqdn, string $path, bool $recursive = false): string
    {
        return md5($siteRef .'_'. $fqdn .'_'. $path . '_' . $recursive);
    }

    /**
     * @param string $domain
     * @param string $fqdn
     * @param string $path
     * @param bool $recursive
     * @return bool
     */
    protected function clearCacheDomain(string $domain, string $fqdn, string $path = '/', bool $recursive = false): bool
    {
        $hash = $this->getSendHash($domain, $fqdn, $path, $recursive);
        if ((self::$multiClearCacheProtection[$hash]??false) === true) {
            return true;
        }

        try {
            $r = $this->getCacheClearApi()->clear($domain, $fqdn, $path, $recursive);
            self::$multiClearCacheProtection[$hash] = $success = (!empty($r) && ($r['error']??true) === false);
        } catch (GuzzleException $e) {
            return false;
        }

        $this->writeLog('User %s has cleared the MYRA_CLOUD cache for domain %s => %s%s (recursive: %s) (success: %s)',
            [
                $this->getBEUser()->user['username'] . ' (uid: ' . $this->getBEUser()->user['uid'] . ')',
                $domain,
                $fqdn,
                $path,
                ($recursive?'true':'false'),
                ($success?'true':'false')
            ]
        );

        return $success;
    }


    /**
     * @param string $domainIdentifier
     * @return string[]
     * @throws \BR\Toolkit\Exceptions\CacheException
     */
    private function getFqdnForSite(string $domainIdentifier): array
    {
        return $this->cacheService->cache(
            'myra_getFqdnForSite_' . $domainIdentifier,
            function () use ($domainIdentifier) {
                $r = $this->getDomainRecordsForDomain($domainIdentifier);
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