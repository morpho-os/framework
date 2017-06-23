<?php
declare(strict_types = 1);
namespace Morpho\Web;

use function Morpho\Base\{
    dasherize, toJson
};
use Morpho\Fs\Path;

class Theme extends Module {
    public const VIEW_FILE_EXT = '.phtml';

    protected $layout = 'index';

    protected $baseDirPaths = [];

    protected $templateEngine;

    private $isLayoutRendered = false;

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

    public function isLayoutRendered(bool $flag = null): bool {
        if ($flag !== null) {
            $this->isLayoutRendered = $flag;
        }
        return $this->isLayoutRendered;
    }

    public function canRender(string $viewPath): bool {
        return false !== $this->absoluteFilePath($viewPath, false);
    }

    /**
     * @Listen render 100
     */
    public function render(array $event): string {
        $args = $event[1];
        $vars = $args['vars'];

        $request = $this->serviceManager->get('request');

        if ($request->isAjax()) {
            return toJson($vars);
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

        if (isset($args['layout'])) {
            $this->layout = dasherize($args['layout']);
        }

        $relFilePath = dasherize($request->controllerName()) . '/' . dasherize($args['view']);
        return $this->renderFile(
            $relFilePath,
            $vars,
            isset($args['instanceVars']) ? $args['instanceVars'] : null
        );
    }

    /**
     * @Listen beforeDispatch 100
     * @param $event
     * /
    public function beforeDispatch(array $event) {
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
     * @Listen afterDispatch 100
     * @param array $event
     */
    public function afterDispatch(array $event) {
        $request = $event[1]['request'];
        if ($request->isDispatched() && false === $this->isLayoutRendered) {
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
                        $this->renderFile($this->layout, ['body' => $response->content()])
                    );
                }
            }
            $this->isLayoutRendered = true;
        }
    }

    public function addBaseDirPath(string $dirPath) {
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
                . implode(PATH_SEPARATOR, $this->baseDirPaths) . "'."
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
}
