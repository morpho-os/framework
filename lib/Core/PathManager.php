<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Di\IHasServiceManager;
use Morpho\Di\IServiceManager;
use Morpho\Fs\Path;

class PathManager implements IHasServiceManager {
    /**
     * @var string
     */
    protected $baseDirPath;

    protected $serviceManager;

    // @TODO
    //protected $useCache;

    public function __construct(string $baseDirPath) {
        $this->baseDirPath = $baseDirPath;
        // @TODO: $this->useCache = $useCache;
    }

    /**
     * @return false|string
     */
    public static function detectBaseDirPath(string $dirPath, bool $throwEx = true) {
        $baseDirPath = null;
        do {
            $path = $dirPath . '/vendor/composer/ClassLoader.php';
            if (is_file($path)) {
                $baseDirPath = $dirPath;
                break;
            } else {
                $chunks = explode(DIRECTORY_SEPARATOR, $dirPath, -1);
                $dirPath = implode(DIRECTORY_SEPARATOR, $chunks);
            }
        } while ($chunks);
        if (null === $baseDirPath) {
            if ($throwEx) {
                throw new \RuntimeException("Unable to find a path of the root directory");
            }
            return null;
        }
        return Path::normalize($baseDirPath);
    }

    public function setBaseDirPath(string $baseDirPath): void {
        $this->baseDirPath = $baseDirPath;
    }

    public function baseDirPath(): string {
        return $this->baseDirPath;
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }
}