<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use Morpho\Base\IFn;
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

        $actionResult = $response['result'];

        $handler = $request->handler();

        if (!isset($actionResult['_path'])) {
            $actionResult['_path'] = dasherize($handler['method']);
        }
        if (false === strpos($actionResult['_path'], '/')) {
            $actionResult['_path'] = $handler['controllerPath'] . '/' . $actionResult['_path'];
        }

        $html = $this->renderView($handler['module'], $actionResult);

        if (!$response->allowAjax() || !$request->isAjax()) {
            $page = $actionResult['_parent'] ?? ['_path' => 'index'];
            $page['body'] = $html;
            $html = $this->renderView($this->pageRenderingModule, $page);
        }

        $response->setBody($html);
        // https://tools.ietf.org/html/rfc7231#section-3.1.1
        $response->headers()['Content-Type'] = 'text/html;charset=utf-8';

        return $request;
    }

    protected function renderView(string $moduleName, $actionResult): string {
        $viewDirPath = $this->moduleIndex->module($moduleName)->viewDirPath();
        $theme = $this->theme;
        $theme->addBaseDirPath($viewDirPath);
        return $theme->render($actionResult);
    }
}
