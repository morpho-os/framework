<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Web\View;

use function Morpho\Base\{
    dasherize, toJson
};
use Morpho\Di\IServiceManager;
use Morpho\Di\IHasServiceManager;
use Morpho\Fs\Path;
use Morpho\Web\Response;

class Theme implements IHasServiceManager {
    public const VIEW_FILE_EXT = '.phtml';
    public const DEFAULT_LAYOUT = 'index';

    protected $baseDirPaths = [];

    protected $templateEngine;

    protected $serviceManager;

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    public function setTemplateEngine($templateEngine): void {
        $this->templateEngine = $templateEngine;
    }

    public function templateEngine() {
        if (null === $this->templateEngine) {
            $this->templateEngine = $templateEngine = $this->serviceManager->get('templateEngine');
            $this->initTemplateEngineVars($templateEngine);
        }
        return $this->templateEngine;
    }

/*    public function canRender(string $viewPath): bool {
        return false !== $this->absoluteFilePath($viewPath, false);
    }*/

    public function renderView(View $view): string {
        $serviceManager = $this->serviceManager;

        $request = $serviceManager->get('request');

        if ($request->isAjax()) {
            return toJson($view->vars());
        }

        $moduleIndex = $serviceManager->get('moduleIndex');

        $viewDirPath = $moduleIndex->moduleMeta($request->moduleName())->viewDirPath();
        $this->appendBaseDirPath($viewDirPath);

        $relFilePath = dasherize($request->controllerName()) . '/' . dasherize($view->name());
        return $this->renderFile(
            $relFilePath,
            $view->vars(),
            $view->properties()
        );
    }

    /**
     * @Listen beforeDispatch -9999
     * /
    public function beforeDispatch($event) {
        //$this->autoDecodeRequestJson();
        /*
        $request = $this->request;
        $header = $request->header('Content-Type');
        if (false !== $header && false !== stripos($header->getFieldValue(), 'application/json')) {
            $data = Json::decode($request->content());
            $request->replace((array) $data);
        }
    }
    */

    public function renderLayout($request): void {
        $params = $request->params();
        if ($params->offsetExists('layout')) {
            $layout = $params->offsetGet('layout');
        } else {
            $layout = $this->newDefaultLayout();
            $params->offsetSet('layout', $layout);
        }
        if ($request->isDispatched() && !$layout->isRendered()) {
            $response = $request->response();
            if ($request->isAjax()) {
                $response->headers()
                    ->addHeaderLine('Content-Type', 'application/json');
                if ($response->isRedirect()) {
                    if ($response->isContentEmpty()) {
                        $locationHeader = $response->headers()->get('Location');
                        $notEncodedContent = ['success' => ['redirect' => $locationHeader->getUri()]];
                        $response->setContent(toJson($notEncodedContent))
                            ->setStatusCode(Response::STATUS_CODE_200)
                            ->getHeaders()->removeHeader($locationHeader);
                    }
                }
            } else {
                $dirPath = $layout->dirPath();
                if ($dirPath) {
                    $this->appendBaseDirPath($dirPath);
                }
                if (!$response->isRedirect()) {
                    $response->setContent(
                        $this->renderFile($layout->name(), ['body' => $response->content()])
                    );
                }
            }
            $layout->isRendered(true);
        }
    }

    public function appendBaseDirPath(string $dirPath): void {
        $this->baseDirPaths[] = $dirPath;
        $this->baseDirPaths = array_values(array_unique($this->baseDirPaths));
    }
    
    public function baseDirPaths(): array {
        return $this->baseDirPaths;
    }
    
    public function clearBaseDirPaths(): void {
        $this->baseDirPaths = [];
    }

    /**
     * @return bool|string
     */
    protected function absoluteFilePath(string $relOrAbsFilePath, bool $throwExIfNotFound = true) {
        $relOrAbsFilePath .= self::VIEW_FILE_EXT;
        if (Path::isAbsolute($relOrAbsFilePath) && is_readable($relOrAbsFilePath)) {
            return $relOrAbsFilePath;
        }
        for ($i = count($this->baseDirPaths()) - 1; $i >= 0; $i--) {
            $baseDirPath = $this->baseDirPaths[$i];
            $filePath = Path::combine($baseDirPath, $relOrAbsFilePath);
            if (is_readable($filePath)) {
                return $filePath;
            }
        }
        if ($throwExIfNotFound) {
            throw new \RuntimeException(
                "Unable to detect an absolute file path for the path '$relOrAbsFilePath', searched in paths:\n'"
                . implode(PATH_SEPARATOR, $this->baseDirPaths) . "'"
            );
        }
        return false;
    }

    protected function initTemplateEngineVars($templateEngine): void {
        $templateEngine->setVars([
            'uri' => $this->serviceManager->get('request')->uri(),
        ]);
    }

    protected function renderFile(string $relFilePath, array $vars, array $instanceVars = null): string {
        $templateEngine = $this->templateEngine();
        if (null !== $instanceVars) {
            $templateEngine->mergeVars($instanceVars);
        }
        return $templateEngine->renderFile($this->absoluteFilePath($relFilePath), $vars);
    }

    protected function newDefaultLayout(): View {
        return new View(self::DEFAULT_LAYOUT);
    }
}
