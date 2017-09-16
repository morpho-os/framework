<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code;

use ReflectionFunction;

class ReflectionNamespace {
    private $name;
    private $classTypes;
    private $functions;
    private $isGlobal;
    private $filePath;

    public function __construct(string $filePath, ?string $name, iterable $classTypes, iterable $functions, bool $isGlobal) {
        $this->filePath = $filePath;
        $this->name = $name;
        $this->classTypes = $classTypes;
        $this->functions = $functions;
        $this->isGlobal = $isGlobal;
    }

    public function filePath(): string {
        return $this->filePath;
    }

    public function isGlobal(): bool {
        return $this->isGlobal;
    }

    public function name(): ?string {
        return $this->name;
    }

    public function classTypes(): iterable {
        require_once $this->filePath;
        foreach ($this->classTypes as $class) {
            yield new ReflectionClass($class);
        }
    }

    public function functions(): iterable {
        require_once $this->filePath;
        foreach ($this->functions as $function) {
            yield new ReflectionFunction($function);
        }
    }
}