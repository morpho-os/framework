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
     * @var ?bool
     */
    private $fallbackMode;

    /**
     * @var ?string
     */
    private $hostName;

    public function __construct(string $name, ModuleFs $fs, ?string $hostName) {
        parent::__construct($name, $fs);
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
        $this->fs()->writeConfig($config);
        $this->config = null; // init config on the next request.
    }

    public function isFallbackMode(bool $flag = null): bool {
        if (null !== $flag) {
            $this->fallbackMode = $flag;
        } else {
            $this->initConfig();
        }
        return $this->fallbackMode;
    }
    
    public function setFs(ModuleFs $fs): void {
        parent::setFs($fs);
        $this->config = null; // init config on the next request.
    }

    private function initConfig(): void {
        if (null === $this->config) {
            if ($this->fs->canLoadConfigFile()) {
                $this->config = $this->fs->loadConfigFile();
                $this->fallbackMode = false;
            } else {
                $this->config = $this->fs->loadFallbackConfigFile();
                $this->fallbackMode = true;
            }
        }
    }
}