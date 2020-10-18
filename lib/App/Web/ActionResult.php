<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\App\Web\Messages\THasMessenger;
use Morpho\App\Web\Messages\IHasMessenger;

class ActionResult extends \ArrayObject implements IHasMessenger {
    use THasMessenger;

    private bool $allowAjax = false;

    private ?string $path = null;

    private ?ActionResult $page = null;

    private array $formats;
 
    public function __construct($vars = []) {
        if (is_scalar($vars)) {
            $vars = ['result' => $vars];
        } elseif ($vars instanceof \ArrayObject) {
            $vars = $vars->getArrayCopy();
        }
        parent::__construct($vars);
        $this->formats = [
            ContentFormat::HTML,
//            ContentFormat::JSON,
//            ContentFormat::XML,
//            ContentFormat::TEXT => false,
//            ContentFormat::BIN => false,
        ];
    }

    public function setFormats($formats): self {
        $this->formats = (array) $formats;
        return $this;
    }

    public function formats(): array {
        return $this->formats;
    }

    /**
     * @return bool|self
     */
    public function allowAjax(bool $flag = null) {
        if ($flag !== null) {
            $this->allowAjax = $flag;
            return $this;
        }
        return $this->allowAjax;
    }

    /**
     * @param string|ActionResult $actionResult
     */
    public function setPage($actionResult): self {
        if (is_string($actionResult)) {
            $actionResult = new ActionResult();
            $actionResult->setPath($actionResult);
        }
        $this->page = $actionResult;
        return $this;
    }

    public function page(): ?self {
        return $this->page;
    }

    public function setPath(string $path): self {
        $this->path = $path;
        return $this;
    }

    public function path(): ?string {
        return $this->path;
    }
}
