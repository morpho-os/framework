<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Web\View;

class View {
    /**
     * @var string
     */
    protected $name;
    /**
     * @var array
     */
    private $vars;
    /**
     * @var array
     */
    private $properties;
    /**
     * @var bool
     */
    private $isRendered;

    public function __construct(string $name, array $vars = null, array $properties = null, bool $isRendered = null) {
        $this->name = $name;
        $this->vars = (array)$vars;
        $this->properties = (array)$properties;
        $this->isRendered = (bool) $isRendered;
    }

    public function isRendered(bool $flag = null): bool {
        if (null !== $flag) {
            $this->isRendered = $flag;
        }
        return $this->isRendered;
    }

    public function name(): string {
        return $this->name;
    }

    public function setProperties(array $properties) {
        $this->properties = $properties;
    }

    public function properties(): array {
        return $this->properties;
    }

    public function setVars(array $vars) {
        $this->vars = $vars;
    }

    public function vars(): array {
        return $this->vars;
    }
}