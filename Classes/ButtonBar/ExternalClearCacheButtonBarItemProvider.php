<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\ButtonBar;


use CPSIT\CpsMyraCloud\AdapterProvider\ExternalCacheProvider;
use CPSIT\CpsMyraCloud\Domain\Enum\Typo3CacheType;
use CPSIT\CpsMyraCloud\Service\PageService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;

class ExternalClearCacheButtonBarItemProvider
{
    private PageService $pageService;
    private IconFactory $iconFactory;

    private int $cacheTypeCache = Typo3CacheType::UNKNOWN;
    private string $cacheId = '';

    /**
     * @param PageService $pageService
     * @param IconFactory $iconFactory
     */
    public function __construct(PageService $pageService, IconFactory $iconFactory)
    {
        $this->pageService = $pageService;
        $this->iconFactory = $iconFactory;
    }

    /**
     * Get buttons
     *
     * @param array $params
     * @param ButtonBar $buttonBar
     * @return array
     */
    public function clearPageCache(array $params, ButtonBar $buttonBar): array
    {
        $buttons = $params['buttons'];
        if (!$this->isSupported()) {
            return $buttons;
        }

        $provider = ExternalCacheProvider::getDefaultProviderItem();
        if ($provider && $provider->canInteract()) {
            $lang = $this->getLanguageService();
            $clearCacheButton = $buttonBar->makeLinkButton();
            $clearCacheButton->setIcon($this->iconFactory->getIcon($provider->getCacheIconIdentifier(), Icon::SIZE_SMALL));
            $clearCacheButton->setTitle($lang->sL($provider->getCacheTitle()))
                ->setHref('#')
                ->setClasses($provider->getTypo3CssClass())
                ->setOnClick($provider->getRequireJsCall($this->getIdentifier(), $this->getCacheType()));

            if (!isset($buttons[ButtonBar::BUTTON_POSITION_RIGHT])) {
                $buttons[ButtonBar::BUTTON_POSITION_RIGHT] = [];
            }

            $buttons[ButtonBar::BUTTON_POSITION_RIGHT][1][] = $clearCacheButton;
        }

        return $buttons;
    }

    /**
     * Returns LanguageService
     *
     * @return LanguageService
     */
    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @return string
     */
    private function getIdentifier(): string
    {
        if ($this->cacheId !== '')
            return $this->cacheId;

        $id = (string)($this->getRequest()->getQueryParams()['id']??'');
        if ($this->getCacheType() === Typo3CacheType::PAGE) {
            if(!is_numeric($id))
                return '';

            return $this->cacheId = $id;
        } elseif ($this->getCacheType() === Typo3CacheType::FILE_ADMIN) {
            if (strpos($id, '1:/') === 0) {
                return $this->cacheId = substr($id, 2);
            } else {
                return $this->cacheId = '/';
            }
        }

        return '';
    }

    /**
     * @return bool
     */
    private function isSupported(): bool
    {
        return
            $this->isModuleSupported() &&
            $this->isPageTypeSupported();
    }

    /**
     * @return bool
     */
    private function isPageTypeSupported(): bool
    {
        if ($this->getCacheType() === Typo3CacheType::PAGE) {
            $pageUid = (int)$this->getIdentifier();
            if ($pageUid <= 0)
                return false;

            return $this->pageService->getPage($pageUid) !== null;
        } elseif ($this->getCacheType() === Typo3CacheType::FILE_ADMIN) {
            $path = $this->getIdentifier();
            return !empty($path);
        }

        return false;
    }

    /**
     * @return bool
     */
    private function isModuleSupported(): bool
    {
        return $this->getCacheType() > Typo3CacheType::UNKNOWN;
    }

    /**
     * @return int
     */
    private function getCacheType(): int
    {
        if ($this->cacheTypeCache !== Typo3CacheType::UNKNOWN) {
            return $this->cacheTypeCache;
        }

        $route = $this->getBackendRoute();
        if ($route === '/module/file/FilelistList') {
            return $this->cacheTypeCache = Typo3CacheType::FILE_ADMIN;
        } elseif (in_array($route, [
            '/module/web/layout',
            '/module/web/list',
            '/module/web/ViewpageView'
        ])) {
            return $this->cacheTypeCache = Typo3CacheType::PAGE;
        }

        return $this->cacheTypeCache = Typo3CacheType::INVALID;
    }

    /**
     * @return string
     */
    private function getBackendRoute(): string
    {
        return $this->getRequest()->getQueryParams()['route']??'';
    }

    /**
     * @return ServerRequestInterface
     */
    private function getRequest(): ServerRequestInterface
    {
        /** @var ServerRequest $request */
        $request = $GLOBALS['TYPO3_REQUEST']??ServerRequestFactory::fromGlobals();
        return $request;
    }
}