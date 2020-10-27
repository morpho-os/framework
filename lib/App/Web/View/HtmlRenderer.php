<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use Morpho\Base\IFn;
use Morpho\App\Web\ActionResult;
use function Morpho\Base\dasherize;

class HtmlRenderer implements IFn {
    private $theme;

    private $moduleIndex;

    private string $pageRenderingModule;

    public function __construct($theme, $moduleIndex, string $pageRenderingModule) {
        $this->theme = $theme;
        $this->moduleIndex = $moduleIndex;
        $this->pageRenderingModule = $pageRenderingModule;
    }

    public function __invoke($request) {
        $response = $request->response();
        if ($response->allowAjax() && $request->isAjax()) {
            $html = $this->renderBody($request);
        } else {
            $body = $this->renderBody($request);
            $actionResult = $response['result'];
            $page = $actionResult['_parent'] ?? ['_path' => 'index'];
            $html = $this->renderPage($page, $body);
        }
        $response = $request->response();
        $response->setBody($html);
        // https://tools.ietf.org/html/rfc7231#section-3.1.1
        $response->headers()['Content-Type'] = 'text/html;charset=utf-8';
        return $request;
    }

    public function renderBody($request): string {
        $actionResult = $request->response()['result'];
        $handler = $request->handler();
        if (!isset($actionResult['_path'])) {
            $actionResult['_path'] = dasherize($handler['method']);
        }
        if (false === strpos($actionResult['_path'], '/')) {
            $actionResult['_path'] = $handler['controllerPath'] . '/' . $actionResult['_path'];
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
}
