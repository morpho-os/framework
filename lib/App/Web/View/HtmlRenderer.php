<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use function Morpho\Base\dasherize;
use Morpho\Base\IFn;
use Morpho\Ioc\IServiceManager;
use Morpho\App\Web\Request;

class HtmlRenderer implements IFn {
    protected $serviceManager;

    public function __construct(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @param Request $request
     */
    public function __invoke($request): void {
        $serviceManager = $this->serviceManager;

        /** @var \Morpho\App\Web\Response $response */
        $response = $request->response();

        // 1. Render view
        $moduleName = $request->moduleName();
        /** @var Page $page */
        $page = $response['result'];
        $view = $page->view();
        if (!$view->dirPath()) {
            $view->setDirPath(dasherize($request->controllerName()));
        }
        $renderedView = $this->render($moduleName, $view);

        // 2. Render Layout
        $moduleName = $serviceManager->config()['view']['layoutModule'];
        $layout = $page->layout();
        $layout['body'] = $renderedView;
        $renderedLayout = $this->render($moduleName, $layout);

        $response->setBody($renderedLayout);
        // https://tools.ietf.org/html/rfc7231#section-3.1.1
        $response->headers()['Content-Type'] = 'text/html;charset=utf-8';
    }

    protected function render(string $moduleName, View $view): string {
        $serviceManager = $this->serviceManager;
        $moduleIndex = $serviceManager['moduleIndex'];
        /** @var Theme $theme */
        $theme = $serviceManager['theme'];
        $viewDirPath = $moduleIndex->moduleMeta($moduleName)->viewDirPath();
        $theme->appendBaseDirPath($viewDirPath);
        return $theme->render($view);
    }
}
