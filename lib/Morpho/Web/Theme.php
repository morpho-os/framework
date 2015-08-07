<?php
namespace Morpho\Web;

use Morpho\Core\Module;
use Morpho\Fs\Path;

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
     * @return bool
     */
    public function isLayoutRendered($flag = null) {
        if ($flag !== null) {
            $this->isLayoutRendered = $flag;
        }
        return $this->isLayoutRendered;
    }

    /**
     * @param string $viewPath
     * @return bool
     */
    public function canRender($viewPath) {
        return false !== $this->getAbsoluteFilePath($viewPath, false);
    }

    /**
     * @Listen render 100
     * @param $event
     * @return string
     */
    public function render(array $event) {
        $args = $event[1];
        $vars = $args['vars'];
        if (isset($args['layout'])) {
            $this->layout = dasherize($args['layout']);
        }
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

    public function addBasePath($path) {
        array_unshift($this->basePaths, $path);
    }

    protected function getAbsoluteFilePath($relOrAbsFilePath, $throwExIfNotFound = true) {
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
                "Unable to detect an absolute file path for the path '$relOrAbsFilePath', searched in paths: '"
                . implode(PATH_SEPARATOR, $this->basePaths) . "'."
            );
        }
        return false;
    }

    protected function initTemplateEngineVars($templateEngine) {
        $templateEngine->setVars([
            'baseUri' => $this->serviceManager->get('request')->getBaseRelativeUri()
        ]);
    }

    protected function renderFile($path, array $vars, array $instanceVars = null) {
        $templateEngine = $this->getTemplateEngine();
        if (null !== $instanceVars) {
            $templateEngine->mergeVars($instanceVars);
        }
        return $templateEngine->renderFile($this->getAbsoluteFilePath($path), $vars);
    }
}
