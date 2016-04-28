<?php
declare(strict_types = 1);

namespace Morpho\Web;

use function Morpho\Base\{
    dasherize, encodeJson
};
use Morpho\Core\Module;
use Morpho\Fs\Path;

abstract class Theme extends Module {
    protected $suffix = '.phtml';

    protected $layout = 'index';

    protected $baseDirPaths = [];

    protected $templateEngine;

    private $isLayoutRendered = false;

    private $isThemeDirAdded = false;

    public function setTemplateEngine($templateEngine) {
        $this->templateEngine = $templateEngine;
    }

    public function getTemplateEngine() {
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
        return false !== $this->getAbsoluteFilePath($viewPath, false);
    }

    /**
     * @Listen render 100
     */
    public function render(array $event): string {
        $args = $event[1];
        $vars = $args['vars'];

        $request = $this->serviceManager->get('request');

        if ($request->isAjax()) {
            $request->getResponse()
                ->getHeaders()
                ->addHeaderLine('Content-Type', 'application/json');
            return encodeJson($vars);
        }

        if (!$this->isThemeDirAdded) {
            $this->addBaseDirPath($this->getClassDirPath() . '/' . VIEW_DIR_NAME);
            $this->isThemeDirAdded = true;
        }
        $this->addBaseDirPath(
            $this->getParent('ModuleManager')->getModuleFs()->getModuleViewDirPath($request->getModuleName())
        );

        if (isset($args['layout'])) {
            $this->layout = dasherize($args['layout']);
        }
        $vars['node'] = $args['node'];
        $filePath = dasherize($vars['node']->getName()) . '/' . dasherize($args['name']);
        return $this->renderFile(
            $filePath,
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
        $header = $request->getHeader('Content-Type');
        if (false !== $header && false !== stripos($header->getFieldValue(), 'application/json')) {
            $data = Json::decode($request->getContent());
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
            if (!$request->isAjax()) {
                $response = $request->getResponse();
                $response->setContent(
                    $this->renderFile($this->layout, ['body' => $response->getContent(), 'node' => $this])
                );
            }
            $this->isLayoutRendered = true;
        }
    }

    public function addBaseDirPath(string $dirPath) {
        if (false === array_search($dirPath, $this->baseDirPaths, true)) {
            array_unshift($this->baseDirPaths, $dirPath);
        }
    }

    /**
     * @return bool|string
     */
    protected function getAbsoluteFilePath(string $relOrAbsFilePath, bool $throwExIfNotFound = true) {
        $relOrAbsFilePath .= $this->suffix;
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

    protected function initTemplateEngineVars($templateEngine) {
        $templateEngine->setVars([
            'uri' => $this->serviceManager->get('request')->uri(),
        ]);
    }

    protected function renderFile(string $filePath, array $vars, array $instanceVars = null): string {
        $templateEngine = $this->getTemplateEngine();
        if (null !== $instanceVars) {
            $templateEngine->mergeVars($instanceVars);
        }
        return $templateEngine->renderFile($this->getAbsoluteFilePath($filePath), $vars);
    }
}
