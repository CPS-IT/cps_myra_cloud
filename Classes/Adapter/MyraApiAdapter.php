<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Myracloud\WebApi\Endpoint\CacheClear;
use Myracloud\WebApi\Middleware\Signature;
use Psr\Http\Message\RequestInterface;

class MyraApiAdapter
{
    /**
     * @var CacheClear|null
     */
    private ?CacheClear $myraCacheApi;

    /**
     *
     */
    public function __construct()
    {
        $this->myraCacheApi = $this->getCacheClearApi();
    }

    /**
     * @return CacheClear|null
     */
    private function getCacheClearApi(): ?CacheClear
    {
        $client = $this->getMyraClient();
        try {
            return $client !== null ? new CacheClear($client) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param string $domain
     * @param string $fqdn
     * @param string $resources
     * @return bool
     * @throws \Exception
     */
    public function clearCache(string $domain, string $fqdn, string $resources): bool
    {
        if ($this->myraCacheApi === null) {
            throw new \Exception('Could not connect to MyraApi (missing credentials?)');
        }

        try {
            $response = $this->myraCacheApi->clear($domain, $fqdn, $resources, true);
        } catch (GuzzleException $e) {
            throw new \Exception('Could not connect to MyraApi (connection failed)');
        }

        return true;
    }

    /**
     * @return ClientInterface|null
     */
    private function getMyraClient(): ?ClientInterface
    {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());

        $signature = new Signature('secret', 'apiKey');
        $stack->push(
            Middleware::mapRequest(
                function (RequestInterface $request) use ($signature) {
                    return $signature->signRequest($request);
                }
            )
        );
        $config = [];
        return new Client(
            array_merge(
                [
                    'base_uri' => 'https://' . 'api.myracloud.com' . '/' . 'en' . '/rapi',
                    'handler'  => $stack,
                ],
                $config
            )
        );
    }
}