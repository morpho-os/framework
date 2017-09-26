<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Core\Fs as BaseFs;
use Morpho\Di\IServiceManager;
use Morpho\Di\IWithServiceManager;

class Fs extends BaseFs implements IWithServiceManager {
    protected $serviceManager;

    /**
     * @var ?string
     */
    private $publicDirPath;

    public function setPublicDirPath(string $publicDirPath): void {
        $this->publicDirPath = $publicDirPath;
    }

    public function publicDirPath(): string {
        if (null === $this->publicDirPath) {
            $this->publicDirPath = $this->baseDirPath . '/' . PUBLIC_DIR_NAME;
        }
        return $this->publicDirPath;
    }

    public function cacheDirPath(): string {
        return $this->serviceManager->get('site')->fs()->cacheDirPath();
    }

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }
}