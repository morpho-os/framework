<?php
declare(strict_types=1);

namespace Morpho\Web;

use function Morpho\Base\dasherize;
use Morpho\Core\Module;
use Morpho\Fs\Path;
use Zend\Json\Json;

abstract class Theme extends Module {
    protected $suffix = '.phtml';

    protected $layout = 'index';

    protected $basePaths = [];

    protected $templateEngine;

    private $isLayoutRendered = false;

    private $themeDirAdded = false;

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

    /**
     * @param bool|null $flag
     */
    public function isLayoutRendered($flag = null): bool {
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
            return Json::encode($vars);
        }
        if (isset($args['layout'])) {
            $this->layout = dasherize($args['layout']);
        }
        $vars['node'] = $args['node'];
        return $this->renderFile(
            dasherize($vars['node']->getName()) . '/' . dasherize($args['name']),
            $vars,
            isset($args['instanceVars']) ? $args['instanceVars'] : null
        );
    }

    /**
     * @Listen beforeDispatch 100
     * @param $event
     */
    public function beforeDispatch(array $event) {
        //$this->autoDecodeRequestJson();
        /*
        $request = $this->request;
        $header = $request->getHeader('Content-Type');
        if (false !== $header && false !== stripos($header->getFieldValue(), 'application/json')) {
            $data = Json::decode($request->getContent());
            $request->replace((array) $data);
        }
        */
        if (!$this->themeDirAdded) {
            $this->addBasePath($this->getClassDirPath() . '/' . VIEW_DIR_NAME);
            $this->themeDirAdded = true;
        }

        $request = $event[1]['request'];
        $module = $this->getParent('ModuleManager')->getChild($request->getModuleName());
        $this->addBasePath(
            $module->getClassDirPath() . '/' . VIEW_DIR_NAME
        );
    }

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

    public function addBasePath(string $path) {
        array_unshift($this->basePaths, $path);
    }

    /**
     * @return bool|string
     */
    protected function getAbsoluteFilePath(string $relOrAbsFilePath, bool $throwExIfNotFound = true) {
        $relOrAbsFilePath .= $this->suffix;
        if (Path::isAbsolute($relOrAbsFilePath) && is_readable($relOrAbsFilePath)) {
            return $relOrAbsFilePath;
        }
        foreach ($this->basePaths as $basePath) {
            $filePath = Path::combine($basePath, $relOrAbsFilePath);
            if (is_readable($filePath)) {
                return $filePath;
            }
        }
        if ($throwExIfNotFound) {
            throw new \RuntimeException(
                "Unable to detect an absolute file path for the path '$relOrAbsFilePath', searched in paths:\n'"
                . implode(PATH_SEPARATOR, $this->basePaths) . "'."
            );
        }
        return false;
    }

    protected function initTemplateEngineVars($templateEngine) {
        $templateEngine->setVars([
            'uri' => $this->serviceManager->get('request')->uri(),
        ]);
    }

    protected function renderFile($path, array $vars, array $instanceVars = null): string {
        $templateEngine = $this->getTemplateEngine();
        if (null !== $instanceVars) {
            $templateEngine->mergeVars($instanceVars);
        }
        return $templateEngine->renderFile($this->getAbsoluteFilePath($path), $vars);
    }
}
