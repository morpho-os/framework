<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

class Site implements ISite {
    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    private $hostName;

    public function __construct(string $moduleName, \ArrayObject $config, string $hostName) {
        $this->moduleName = $moduleName;
        $this->config = $config;
        $this->hostName = $hostName;
    }

    public function moduleName(): string {
        return $this->moduleName;
    }

    public function config(): \ArrayObject {
        return $this->config;
    }

    public function hostName(): string {
        return $this->hostName;
    }
}
