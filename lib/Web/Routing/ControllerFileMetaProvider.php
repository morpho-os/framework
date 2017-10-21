<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

namespace Morpho\Web\Routing;

use Morpho\Base\IFn;
use const Morpho\Core\CONTROLLER_SUFFIX;
use const Morpho\Core\LIB_DIR_NAME;
use Morpho\Core\ModuleIndex;
use Morpho\Fs\Directory;

class ControllerFileMetaProvider implements IFn {
    private $moduleIndex;

    public function __construct(ModuleIndex $moduleIndex) {
        $this->moduleIndex = $moduleIndex;
    }

    public function __invoke($modules): iterable {
        $index = $this->moduleIndex;
        foreach ($modules as $moduleName) {
            $paths = $index->moduleMeta($moduleName)['paths'];
            if (isset($paths['controllerDirPath'])) {
                $controllerDirPath = $paths['controllerDirPath'];
            } else {
                if (isset($paths['libDirPath'])) {
                    $libDirPath = $paths['libDirPath'];
                } else {
                    $libDirPath = $paths['baseDirPath'] . '/' . $paths['relDirPath'] . '/' . LIB_DIR_NAME;
                }
                $controllerDirPath = $libDirPath . '/Web';
            }
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