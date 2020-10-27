<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use ArrayObject;
use Morpho\Fs\Path;
use RuntimeException;
use function array_search;
use function array_values;
use function count;
use function implode;
use function is_readable;

class Theme {
    public const VIEW_FILE_EXT = '.phtml';

    protected array $baseDirPaths = [];

    protected TemplateEngine $templateEngine;

    public function __construct(TemplateEngine $templateEngine) {
        $this->templateEngine = $templateEngine;
    }

/*    public function canRender(string $viewPath): bool {
        return false !== $this->absFilePath($viewPath, false);
    }*/

    public function render($actionResult): string {
        return $this->renderFile($actionResult['_path'], $actionResult);
    }

    public function addBaseDirPath(string $dirPath): void {
        $baseDirPaths = $this->baseDirPaths;
        $key = array_search($dirPath, $baseDirPaths);
        if (false !== $key) {
            unset($baseDirPaths[$key]);
        }
        $baseDirPaths[] = $dirPath;
        $this->baseDirPaths = array_values($baseDirPaths);
    }

    public function baseDirPaths(): array {
        return $this->baseDirPaths;
    }

    public function clearBaseDirPaths(): void {
        $this->baseDirPaths = [];
    }

    /**
     * @param string $relOrAbsFilePath
     * @param bool $throwExIfNotFound
     * @return bool|string
     */
    protected function absFilePath(string $relOrAbsFilePath, bool $throwExIfNotFound = true) {
        $relOrAbsFilePath .= self::VIEW_FILE_EXT;
        if (Path::isAbs($relOrAbsFilePath) && is_readable($relOrAbsFilePath)) {
            return $relOrAbsFilePath;
        }
        for ($i = count($this->baseDirPaths()) - 1; $i >= 0; $i--) {
            $baseDirPath = $this->baseDirPaths[$i];
            $filePath = Path::combine($baseDirPath, $relOrAbsFilePath);
            if (is_readable($filePath)) {
                return $filePath;
            }
        }
        if ($throwExIfNotFound) {
            throw new RuntimeException(
                "Unable to detect an absolute file path for the path '$relOrAbsFilePath', searched in paths:\n'"
                . implode(PATH_SEPARATOR, $this->baseDirPaths) . "'"
            );
        }
        return false;
    }

    /**
     * @param string $relFilePath
     * @param ArrayObject|array $vars
     * @return string
     */
    protected function renderFile(string $relFilePath, $vars): string {
        return $this->templateEngine->runFile($this->absFilePath($relFilePath), $vars);
    }
}
