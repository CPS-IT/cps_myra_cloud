<?php
declare(strict_types=1);

namespace Fr\MyraCloud\ButtonBar;


use Fr\MyraCloud\Provider\ExternalCacheProvider;
use Fr\MyraCloud\Service\PageService;
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
        if ($provider) {
            $lang = $this->getLanguageService();
            $uid = $this->getPageUid();
            $clearCacheButton = $buttonBar->makeLinkButton();
            $clearCacheButton->setIcon($this->iconFactory->getIcon($provider->getCacheIconIdentifier(), Icon::SIZE_SMALL));
            $clearCacheButton->setTitle($lang->sL($provider->getCacheTitle()))
                ->setHref('#')
                ->setDataAttributes(['id' => $uid])
                ->setClasses($provider->getTypo3CssClass())
                ->setOnClick($provider->getRequireJsCall($uid));

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
     * @return int
     */
    private function getPageUid(): int
    {
        return (int)($this->getRequest()->getQueryParams()['id']??0);
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
        $pageUid = $this->getPageUid();
        if ($pageUid <= 0) {
            return false;
        }

        return $this->pageService->getPage($pageUid) !== null;
    }

    /**
     * @return bool
     */
    private function isModuleSupported(): bool
    {
        $route = $this->getRequest()->getQueryParams()['route']??'';
        return in_array($route, [
            '/module/web/layout',
            '/module/web/list',
            '/module/web/ViewpageView',
        ]);
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