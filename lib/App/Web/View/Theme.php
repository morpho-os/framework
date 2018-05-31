<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use Morpho\Fs\Path;

class Theme {
    public const VIEW_FILE_EXT = '.phtml';

    protected $baseDirPaths = [];

    protected $templateEngine;

    public function __construct(TemplateEngine $templateEngine) {
        $this->templateEngine = $templateEngine;
    }

/*    public function canRender(string $viewPath): bool {
        return false !== $this->absFilePath($viewPath, false);
    }*/

    public function render(View $view): string {
        return $this->renderFile($view->path(), $view->vars());
    }

    public function appendBaseDirPath(string $dirPath): void {
        $baseDirPaths = $this->baseDirPaths;
        $key = \array_search($dirPath, $baseDirPaths);
        if (false !== $key) {
            unset($baseDirPaths[$key]);
        }
        $baseDirPaths[] = $dirPath;
        $this->baseDirPaths = \array_values($baseDirPaths);
    }
    
    public function baseDirPaths(): array {
        return $this->baseDirPaths;
    }
    
    public function clearBaseDirPaths(): void {
        $this->baseDirPaths = [];
    }

    /**
     * @return bool|string
     */
    protected function absFilePath(string $relOrAbsFilePath, bool $throwExIfNotFound = true) {
        $relOrAbsFilePath .= self::VIEW_FILE_EXT;
        if (Path::isAbs($relOrAbsFilePath) && \is_readable($relOrAbsFilePath)) {
            return $relOrAbsFilePath;
        }
        for ($i = \count($this->baseDirPaths()) - 1; $i >= 0; $i--) {
            $baseDirPath = $this->baseDirPaths[$i];
            $filePath = Path::combine($baseDirPath, $relOrAbsFilePath);
            if (\is_readable($filePath)) {
                return $filePath;
            }
        }
        if ($throwExIfNotFound) {
            throw new \RuntimeException(
                "Unable to detect an absolute file path for the path '$relOrAbsFilePath', searched in paths:\n'"
                . \implode(PATH_SEPARATOR, $this->baseDirPaths) . "'"
            );
        }
        return false;
    }

    /**
     * @param \ArrayObject|array $vars
     */
    protected function renderFile(string $relFilePath, $vars): string {
        return $this->templateEngine->runFile($this->absFilePath($relFilePath), $vars);
    }
}
