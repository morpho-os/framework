<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use Morpho\Base\IFn;
use Morpho\Ioc\IServiceManager;
use Morpho\App\Web\Request;
use function Morpho\Base\dasherize;

class HtmlRenderer implements IFn {
    protected $serviceManager;

    public function __construct(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @param Request $request
     */
    public function __invoke($request): void {
        $body = $this->renderBody($request);
        $this->renderPage($body, $request);
    }

    protected function renderBody($request): string {
        /** @var \Morpho\App\Web\Response $response */
        $response = $request->response();

        /** @var ViewResult $view */
        $viewResult = $response['result'];
        if (!$viewResult instanceof ViewResult) {
            throw new \UnexpectedValueException();
        }

        $handler = $request->handler();

        $viewPath = $viewResult->path();
        if (false === strpos($viewPath, '/')) {
            $viewResult->setPath($handler['controllerPath'] . '/' . $viewPath);
        }

        return $this->renderView($handler['module'], $viewResult);
    }

    protected function renderPage(string $body, $request): void {
        $response = $request->response();

        $viewResult = $response['result'];

        $page = $viewResult->parent() ?: $this->mkPage();
        $page->vars()['body'] = $body;

        $serviceManager = $this->serviceManager;
        $moduleName = $serviceManager->conf()['view']['pageRenderer'];
        $renderedPage = $this->renderView($moduleName, $page);

        $response->setBody($renderedPage);
        // https://tools.ietf.org/html/rfc7231#section-3.1.1
        $response->headers()['Content-Type'] = 'text/html;charset=utf-8';
    }

    protected function renderView(string $moduleName, ViewResult $viewResult): string {
        $serviceManager = $this->serviceManager;
        $moduleIndex = $serviceManager['serverModuleIndex'];
        /** @var Theme $theme */
        $theme = $serviceManager['theme'];
        $viewDirPath = $moduleIndex->module($moduleName)->viewDirPath();
        $theme->appendBaseDirPath($viewDirPath);
        return $theme->render($viewResult);
    }

    protected function mkPage(): ViewResult {
        return new ViewResult('index');
    }
}
