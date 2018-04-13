<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Core;

class Site {
    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @var array
     */
    protected $config;

    public function __construct(string $moduleName, \ArrayObject $config) {
        $this->moduleName = $moduleName;
        $this->config = $config;
    }

    public function moduleName(): string {
        return $this->moduleName;
    }

    public function config(): \ArrayObject {
        return $this->config;
    }
}
