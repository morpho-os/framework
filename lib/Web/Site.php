<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types = 1);
namespace Morpho\Web;

class Site extends Module {
    protected $config;

    /**
     * @var ?string
     */
    private $hostName;

    public function __construct(string $name, ModulePathManager $pathManager, ?string $hostName) {
        parent::__construct($name, $pathManager);
        $this->hostName = $hostName;
    }

    public function hostName(): ?string {
        return $this->hostName;
    }

    public function setPathManager(ModulePathManager $pathManager): void {
        parent::setPathManager($pathManager);
        $this->config = null; // init config on the next request.
    }

    protected function newConfig() {
        return new SiteConfig($this->pathManager);
    }
}