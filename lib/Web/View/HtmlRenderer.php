<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use function Morpho\Base\dasherize;
use Morpho\Base\IFn;
use Morpho\Ioc\IServiceManager;
use Morpho\Web\Request;

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

        // 1. Render view
        $moduleName = $request->moduleName();
        /** @var Page $page */
        $page = $request['page'];
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

        /** @var \Morpho\Web\Response $response */
        $response = $request->response();
        $response->setBody($renderedLayout);
        $response->headers()['Content-Type'] = 'text/html; charset=UTF-8';
    }

    protected function render(string $moduleName, View $view): string {
        $serviceManager = $this->serviceManager;
        $moduleIndex = $serviceManager->get('moduleIndex');
        /** @var Theme $theme */
        $theme = $serviceManager->get('theme');
        $viewDirPath = $moduleIndex->moduleMeta($moduleName)->viewDirPath();
        $theme->appendBaseDirPath($viewDirPath);
        return $theme->render($view);
    }
}