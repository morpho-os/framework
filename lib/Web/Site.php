<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types = 1);
namespace Morpho\Web;

class Site extends Module {
    /**
     * @var ?array
     */
    private $config;

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

    public function setConfig(array $config): void {
        $this->config = $config;
    }

    public function config(): array {
        $this->initConfig();
        return $this->config;
    }

    public function reloadConfig(): array {
        $this->config = null;
        $this->initConfig();
        return $this->config;
    }

    public function writeConfig(array $config): void {
        $this->pathManager()->writeConfig($config);
        $this->config = null; // init config on the next request.
    }

    public function setPathManager(ModulePathManager $pathManager): void {
        parent::setPathManager($pathManager);
        $this->config = null; // init config on the next request.
    }

    private function initConfig(): void {
        $this->config = $this->normalizeConfig($this->pathManager->loadConfigFile());
    }

    protected function normalizeConfig(array $config): array {
        if (!isset($config['modules'])) {
            $config['modules'] = [];
        }
        foreach ($config['modules'] as $name => $conf) {
            if (is_numeric($name)) {
                $config['modules'][$conf] = [];
                unset($config['modules'][$name]);
            }
        }
        return $config;
    }
}