<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use Morpho\App\IActionResult;

class HtmlResult implements IActionResult {
    /**
     * @var string
     */
    protected $path;

    /**
     * @var null|string
     */
    //protected $dirPath;

    /**
     * @var array|\ArrayObject
     */
    protected $vars;

    /**
     * @var HtmlResult|null
     */
    private $parent;

    /**
     * @param string $path
     * @param array|null|\ArrayObject $vars
     * @param HtmlResult|null|string $parent
     */
    public function __construct(string $path, $vars = null, $parent = null) {
        $this->path = $path;
        if (null === $vars) {
            $vars = [];
        }
        $this->vars = \is_array($vars) ? new \ArrayObject($vars) : $vars;
        $this->parent = null !== $parent ? $this->normalizeParent($parent) : $parent;
    }

    /*public function setName(string $name): void {
        $this->name = $name;
    }*/

/*    public function name(): string {
        return $this->name;
    }*/

    public function vars(): \ArrayObject {
        return $this->vars;
    }

/*    public function setDirPath(string $dirPath): void {
        $this->dirPath = $dirPath;
    }

    public function dirPath(): ?string {
        return $this->dirPath;
    }*/

    public function setPath(string $path): void {
        $this->path = $path;
    }

    public function path(): string {
        return $this->path;//Path::combine($this->dirPath, $this->name);
    }

    /**
     * @param string|HtmlResult $viewResult
     */
    public function setParent($viewResult): void {
        $this->parent = $this->normalizeParent($viewResult);
    }

    public function parent(): ?HtmlResult {
        return $this->parent;
    }

    private function normalizeParent($parent): HtmlResult {
        return is_string($parent) ? new HtmlResult($parent) : $parent;
    }

    public function __invoke($serviceManager) {
        $request = $serviceManager['request'];
        $request->response()['result'] = $this;
        $renderer = $serviceManager['htmlRenderer'];
        if ($request->isAjax()) {
            $body = $renderer->renderBody($request);
            $request->response()->setBody($body);
        } else {
            $renderer->__invoke($request);
        }
    }
}
