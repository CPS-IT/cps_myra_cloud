<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Controller;

use Fr\MyraCloud\Service\PageService;
use Fr\MyraCloud\Service\SiteService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExternalClearCacheController
{
    private ResponseFactoryInterface $responseFactory;
    private PageService $pageService;
    private SiteService $siteService;

    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param PageService $pageService
     * @param SiteService $siteService
     */
    public function __construct(ResponseFactoryInterface $responseFactory, PageService $pageService, SiteService $siteService)
    {
        $this->responseFactory = $responseFactory;
        $this->pageService = $pageService;
        $this->siteService = $siteService;
    }

    public function clearPageCache(ServerRequestInterface $request): ResponseInterface
    {
        $clearAll = (bool)($request->getQueryParams()['clearAll']??false);
        $pageUid = (int)($request->getQueryParams()['uid']??0);
        $page = $this->pageService->getPage($pageUid);
        $sites = $this->siteService->getSitesForClearance($page, $clearAll);


        return $this->getJsonResponse(['status' => 'success']);
    }

    /**
     * @param array $data
     * @return ResponseInterface
     */
    private function getJsonResponse(array $data): ResponseInterface
    {
        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(
            (string)json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS)
        );
        return $response;
    }
}