<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Reflection;

//use ReflectionClass as BaseReflectionClass;

use Morpho\Base\NotImplementedException;

class ReflectionClass/* extends BaseReflectionClass*/ {
    private $name;
    private $filePath;

    public function __construct(string $filePath, string $name) {
        $this->filePath = $filePath;
        $this->name = $name;
    }

    public function parentClasses(bool $appendSelf = true): array {
        $rClasses = [];
        $rClass = $this;
        while ($rClass = $rClass->parentClass()) {
            $rClasses[] = $rClass;
        }
        $rClasses = \array_reverse($rClasses);
        if ($appendSelf) {
            $rClasses[]= $this;
        }
        return $rClasses;
    }

    /**
     * @return ReflectionClass|false
     */
    public function parentClass() {
        throw new NotImplementedException();
    }

    public function isTrait(): bool {

    }

    public function isInterface(): bool {

    }
}
