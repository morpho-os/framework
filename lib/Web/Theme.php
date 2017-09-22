<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Web;

use function Morpho\Base\{
    dasherize, toJson
};
use Morpho\Core\View;
use Morpho\Fs\Path;

class Theme extends Module {
    public const VIEW_FILE_EXT = '.phtml';
    public const DEFAULT_LAYOUT = 'index';

    protected $baseDirPaths = [];

    protected $templateEngine;

    private $isThemeDirAdded = false;

    public function setTemplateEngine($templateEngine) {
        $this->templateEngine = $templateEngine;
    }

    public function templateEngine() {
        if (null === $this->templateEngine) {
            $this->templateEngine = $templateEngine = $this->serviceManager->get('templateEngine');
            $this->initTemplateEngineVars($templateEngine);
        }
        return $this->templateEngine;
    }

    public function canRender(string $viewPath): bool {
        return false !== $this->absoluteFilePath($viewPath, false);
    }

    /**
     * @Listen render -9999
     */
    public function render($event): string {
        /** @var \Morpho\Core\View $view */
        $view = $event[1]['view'];

        $request = $this->serviceManager->get('request');

        if ($request->isAjax()) {
            return toJson($view->vars());
        }

        if (!$this->isThemeDirAdded) {
            if (get_class($this) !== __CLASS__) {
                $this->addBaseDirPath($this->viewDirPath());
            }
            $this->isThemeDirAdded = true;
        }

        $this->addBaseDirPath(
            $this->parent('ModuleManager')
                ->offsetGet($request->moduleName())
                ->viewDirPath()
        );

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

    /**
     * @Listen afterDispatch -9999
     */
    public function afterDispatch($event) {
        $request = $event[1]['request'];
        if ($request->hasInternalParam('layout')) {
            $layout = $request->internalParam('layout');
        } else {
            $layout = $this->newDefaultLayout();
            $request->setInternalParam('layout', $layout);
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
                if (!$response->isRedirect()) {
                    $response->setContent(
                        $this->renderFile($layout->name(), ['body' => $response->content()])
                    );
                }
            }
            $layout->isRendered(true);
        }
    }

    public function addBaseDirPath(string $dirPath): void {
        if (false === array_search($dirPath, $this->baseDirPaths, true)) {
            array_unshift($this->baseDirPaths, $dirPath);
        }
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
        foreach ($this->baseDirPaths as $basePath) {
            $filePath = Path::combine($basePath, $relOrAbsFilePath);
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
