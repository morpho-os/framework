<?php //declare(strict_types=1);
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
        if (!$request->isDispatched()) {
            return;
        }
        /** @var \Morpho\Web\Response $response */
        $response = $request->response();
        if ($response->isRedirect()) {
            return;
        }

        $serviceManager = $this->serviceManager;
        $moduleIndex = $serviceManager->get('moduleIndex');
        /** @var Theme $theme */
        $theme = $serviceManager->get('theme');

        // 1. Render view
        $moduleName = $request->moduleName();
        $viewDirPath = $moduleIndex->moduleMeta($moduleName)->viewDirPath();
        $theme->appendBaseDirPath($viewDirPath);

        /** @var Page $page */
        $page = $request->params()['page'];

        $view = $page->view();
        if (!$view->dirPath()) {
            $view->setDirPath(dasherize($request->controllerName()));
        }
        $renderedView = $theme->render($view);
        $view->isRendered(true);

        // 2. Render Layout
        $moduleName = $serviceManager->config()['view']['layoutModule'];
        $viewDirPath = $moduleIndex->moduleMeta($moduleName)->viewDirPath();
        $theme->appendBaseDirPath($viewDirPath);

        $layout = $page->layout();
        $layout->vars()['body'] = $renderedView;
        $renderedLayout = $theme->render($layout);
        $layout->isRendered(true);

        $response->setBody($renderedLayout);
        $response->headers()['Content-Type'] = 'text/html; charset=UTF-8';
        $page->isRendered(true);
    }
}