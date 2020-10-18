<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use Morpho\Base\IFn;
use Morpho\App\Web\Request;
use Morpho\App\Web\ActionResult;
use function Morpho\Base\dasherize;

class HtmlRenderer implements IFn {
    private $request;

    private $theme;

    private $moduleIndex;

    private string $pageRenderingModule;

    public function __construct($request, $theme, $moduleIndex, string $pageRenderingModule) {
        $this->request = $request;
        $this->theme = $theme;
        $this->moduleIndex = $moduleIndex;
        $this->pageRenderingModule = $pageRenderingModule;
    }

    public function __invoke($actionResult): void {
        $request = $this->request;
        if ($actionResult->allowAjax() && $request->isAjax()) {
            $html = $this->renderBody($actionResult);
        } else {
            $body = $this->renderBody($actionResult);
            $html = $this->renderPage($actionResult->page() ?: $this->mkPage(), $body);
        }
        $response = $request->response();
        $response->setBody($html);
        // https://tools.ietf.org/html/rfc7231#section-3.1.1
        $response->headers()['Content-Type'] = 'text/html;charset=utf-8';
    }

    public function renderBody($actionResult): string {
        $request = $this->request;
        $response = $request->response();

        $handler = $request->handler();

        $viewPath = $actionResult->path();
        if (null === $viewPath) {
            $viewPath = dasherize($handler['method']);
        }
        if (false === strpos($viewPath, '/')) {
            $actionResult->setPath($handler['controllerPath'] . '/' . $viewPath);
        }
        return $this->renderView($handler['module'], $actionResult);
    }

    public function renderPage($page, string $body): string {
        $page['body'] = $body;
        return $this->renderView($this->pageRenderingModule, $page);
    }

    protected function renderView(string $moduleName, $actionResult): string {
        $viewDirPath = $this->moduleIndex->module($moduleName)->viewDirPath();
        $theme = $this->theme;
        $theme->addBaseDirPath($viewDirPath);
        return $theme->render($actionResult);
    }

    protected function mkPage(): ActionResult {
        return (new ActionResult())->setPath('index');
    }
}
