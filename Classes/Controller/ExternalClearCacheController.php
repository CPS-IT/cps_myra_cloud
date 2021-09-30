<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Controller;

use CPSIT\CpsMyraCloud\Service\ExternalCacheService;
use CPSIT\CpsMyraCloud\Service\PageService;
use CPSIT\CpsMyraCloud\Service\SiteService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExternalClearCacheController
{
    private ResponseFactoryInterface $responseFactory;
    private ExternalCacheService $externalCacheService;

    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param PageService $pageService
     * @param SiteService $siteService
     */
    public function __construct(ResponseFactoryInterface $responseFactory, ExternalCacheService $externalCacheService)
    {
        $this->responseFactory = $responseFactory;
        $this->externalCacheService = $externalCacheService;
    }

    public function clearPageCache(ServerRequestInterface $request): ResponseInterface
    {
        $clearAll = (bool)($request->getQueryParams()['clearAll']??false);
        $pageUid = (int)($request->getQueryParams()['uid']??0);
        $result = $this->externalCacheService->clearPage($pageUid, $clearAll);


        return $this->getJsonResponse(['status' => $result], (!$result?500:200));
    }

    /**
     * @param array $data
     * @param int $statusCode
     * @return ResponseInterface
     */
    private function getJsonResponse(array $data, int $statusCode = 200): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($statusCode)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(
            (string)json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS)
        );
        return $response;
    }
}