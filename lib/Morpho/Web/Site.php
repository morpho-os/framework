<?php
namespace Morpho\Web;

class Site {
    private $name;
    private $dirPath;
    private $config;
    private $isFallbackConfigUsed;

    const CONFIG_FILE_NAME = 'config.php';

    public function __construct(array $options = array()) {
        foreach ($options as $name => $value) {
            $this->$name = $value;
        }
    }

    public function isFallbackConfigUsed() {
        if (null === $this->isFallbackConfigUsed) {
            throw new \LogicException("The config must be loaded first");
        }
        return $this->isFallbackConfigUsed;
    }

    public function getDirPath($dirName = null) {
        if (null === $dirName) {
            return $this->dirPath;
        }
        if (!in_array($dirName, ['cache', 'config'])) {
            throw new \InvalidArgumentException("Invalid site directory '$dirName' was provided.");
        }

        return $this->dirPath . '/' . constant(strtoupper($dirName) . '_DIR_NAME');
    }

    public function getName() {
        return $this->name;
    }

    public function setConfig(array $config) {
        $this->config = $config;
    }

    public function getConfig() {
        if (null === $this->config) {
            $this->config = $this->loadConfig();
        }

        return $this->config;
    }

    public function getConfigFilePath() {
        return $this->getDirPath('config') . '/' . self::CONFIG_FILE_NAME;
    }

    protected function loadConfig() {
        $filePath = $this->getConfigFilePath();
        if (!file_exists($filePath) || !is_readable($filePath)) {
            /* . (PHP_SAPI == 'cli' ? 'cli-' : '') */
            $filePath = dirname($filePath) . '/fallback.php';
            $this->isFallbackConfigUsed = true;
        } else {
            $this->isFallbackConfigUsed = false;
        }

        return require $filePath;
    }
}
