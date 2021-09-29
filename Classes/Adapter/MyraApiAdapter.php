<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Myracloud\WebApi\Endpoint\AbstractEndpoint;
use Myracloud\WebApi\Endpoint\CacheClear;
use Myracloud\WebApi\Endpoint\Domain;
use Myracloud\WebApi\Middleware\Signature;
use Psr\Http\Message\RequestInterface;

class MyraApiAdapter extends BaseAdapter
{
    private array $clients;

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

    /**
     * @param InstanceConfig $instanceConfig
     * @return CacheClear|null
     */
    private function getCacheClearApi(InstanceConfig $instanceConfig): ?CacheClear
    {
        /** @var ?CacheClear $instance */
        $instance = $this->getEndPointApi(CacheClear::class ,$instanceConfig);
        return $instance;
    }

    /**
     * @param InstanceConfig $instanceConfig
     * @return Domain|null
     */
    private function getDomainApi(InstanceConfig $instanceConfig): ?Domain
    {
        /** @var ?Domain $instance */
        $instance = $this->getEndPointApi(Domain::class ,$instanceConfig);
        return $instance;
    }

    /**
     * @param string $className
     * @param InstanceConfig $config
     * @return AbstractEndpoint|null
     */
    private function getEndPointApi(string $className, InstanceConfig $config): ?AbstractEndpoint
    {
        if (!class_exists($className)) {
            return null;
        }

        $client = $this->getMyraClient($config);
        try {
            return $client !== null ? new $className($client) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param MyraDomain $domain
     * @param PageConfig $cacheConfig
     * @param InstanceConfig $instanceConfig
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function clearCache(MyraDomain $domain, PageConfig $cacheConfig, InstanceConfig $instanceConfig): bool
    {
        $clearCache = $this->getCacheClearApi($instanceConfig);
        $result = $clearCache->clear($domain->getName(), $domain->getFqdn(), $cacheConfig->getResource(), false);
        return true;
    }

    /**
     * @param InstanceConfig $instanceConfig
     * @return MyraDomainList
     * @throws \Exception
     */
    public function getDomains(InstanceConfig $instanceConfig): MyraDomainList
    {
        return MyraDomainList::createWithResult($this->getDomainApi($instanceConfig)->getList());
    }

    /**
     * @param InstanceConfig $instanceConfig
     * @return ClientInterface|null
     */
    private function getMyraClient(InstanceConfig $instanceConfig): ?ClientInterface
    {
        $instanceId = $instanceConfig->getIdentifier();
        if (isset($this->clients[$instanceId])) {
            return $this->clients[$instanceId];
        }

        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());

        $signature = new Signature($instanceConfig->getSecret(), $instanceConfig->getApiKey());
        $stack->push(
            Middleware::mapRequest(
                function (RequestInterface $request) use ($signature) {
                    return $signature->signRequest($request);
                }
            )
        );
        $config = [];
        return $this->clients[$instanceId] = new Client(
            array_merge(
                [
                    'base_uri' => 'https://' . $instanceConfig->getEndpoint() . '/' . 'en' . '/rapi',
                    'handler'  => $stack,
                ],
                $config
            )
        );
    }
}