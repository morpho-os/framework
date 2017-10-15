<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

namespace Morpho\Web\Routing;

use Morpho\Base\IFn;
use const Morpho\Web\CONTROLLER_SUFFIX;
use Morpho\Fs\Directory;

class ControllerFileMetaProvider implements IFn {
    private $moduleProvider;

    public function __construct(\ArrayObject $moduleProvider) {
        $this->moduleProvider = $moduleProvider;
    }

    public function __invoke($modules): iterable {
        $moduleProvider = $this->moduleProvider;
        foreach ($modules as $moduleName => $_) {
            $module = $moduleProvider->offsetGet($moduleName);
            $controllerDirPath = $module->pathManager()->controllerDirPath();
            if (!is_dir($controllerDirPath)) {
                continue;
            }
            foreach (Directory::filePaths($controllerDirPath, '~\w' . CONTROLLER_SUFFIX . '\.php$~') as $filePath) {
                yield [
                    'module' => $moduleName,
                    'filePath' => $filePath,
                ];
            }
        }
    }
}