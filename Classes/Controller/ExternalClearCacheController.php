<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Controller;

use CPSIT\CpsMyraCloud\Domain\Enum\Typo3CacheType;
use CPSIT\CpsMyraCloud\Service\ExternalCacheService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class ExternalClearCacheController
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private ExternalCacheService $externalCacheService
    ) {}

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function clearPageCache(ServerRequestInterface $request): ResponseInterface
    {
        $identifier = $request->getQueryParams()['id']??'0';
        $type = Typo3CacheType::tryFrom((int)$request->getQueryParams()['type']??Typo3CacheType::UNKNOWN->value);

        $result = $this->externalCacheService->clear($type, $identifier);

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
