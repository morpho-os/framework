<?php
namespace Morpho\Web\View;

use Morpho\Base\NotImplementedException;
use function Morpho\Base\trimMore;
use function Morpho\Cli\cmd;
use Morpho\Cli\CommandResult;
use Morpho\Fs\File;
use Morpho\Fs\Path;

class TypeScriptCompiler {
    const MODULE_KIND = 'system';

    const TSCONFIG_FILE = 'tsconfig.json';

    protected $options = [
        // see http://json.schemastore.org/tsconfig for compiler options
        'experimentalDecorators' => true,
        'forceConsistentCasingInFileNames' => true,
        'jsx' => 'preserve',
        'module' => self::MODULE_KIND,
        'moduleResolution' => 'node',
        'newLine' => 'LF',
        'noEmitOnError' => true,
        'noImplicitAny' => true,
        'noImplicitReturns' => true,
        'noUnusedLocals' => true,
        'pretty' => true,
        'removeComments' => true,
        'strictNullChecks' => false,
        'allowJs' => true,
    ];

    public function compileToFile(string $inFilePath, string $outFilePath = null, array $cmdOptions = null): CommandResult {
        return $this->tsc($this->compileToFileOptionsString($inFilePath, $outFilePath), $cmdOptions);
    }

    public function compileToFileOptionsString(string $inFilePath = null, string $outFilePath = null): string {
        $options = [];
        if ($inFilePath) {
            $options['outFile'] = $outFilePath ?: Path::changeExt($inFilePath, 'js');
            $options[] = $inFilePath;
        }
        return $this->optionsString($options);
    }

    public function compileToDir(string $inFilePath, string $outDirPath = null): CommandResult {
        $options = $this->escapeOptions(
            array_merge(
                $this->getOptions(),
                [
                    'outDir' => $outDirPath,
                    $inFilePath,
                ]
            )
        );
        return $this->tsc(implode(' ', $options));
    }

    public function writeTsconfig(string $dirPath): string {
        return File::writeJson($dirPath . '/' . self::TSCONFIG_FILE, $this->getOptions());
    }

    public function version(): string {
        return (string)$this->tsc('--version');
    }

    public function help(): string {
        return (string)$this->tsc('--help');
    }

    public function possibleValuesOfOption(string $optionName): array {
        switch ($optionName) {
            case 'module':
                if (!preg_match('~^.*--module\s+KIND\s+(.*)$~m', $this->help(), $match) || !preg_match_all("~('[^']+')~s", $match[1], $match)) {

                    throw new \RuntimeException("Unable to parse help");
                }
                return trimMore($match[1], "'");
            default:
                throw new NotImplementedException();
        }
    }

    public function unsetOption(string $name) {
        unset($this->options[$name]);
    }

    public function setOption(string $name, $value) {
        $this->options[$name] = $value;
    }

    public function getOption(string $name) {
        return $this->options[$name];
    }

    public function setOptions(array $options) {
        $this->options = $options;
    }

    public function getOptions(): array {
        return $this->options;
    }

    public function optionsString(array $options = null): string {
        return implode(' ', $this->escapeOptions(array_merge($this->getOptions(), (array) $options)));
    }

    protected function escapeOptions(array $options): array {
        $safe = [];
        $sep = ' ';
        foreach ($options as $name => $value) {
            if (is_numeric($name)) {
                $safe[] = escapeshellarg($value);
            } elseif (is_bool($value)) {
                if ($value) {
                    $safe[] = escapeshellarg('--' . $name);
                }
            } else {
                $safe[] = escapeshellarg('--' . $name) . $sep . escapeshellarg($value);
            }
        }
        return $safe;
    }

    protected function tsc(string $argsString, array $cmdOptions = null): CommandResult {
        return cmd('tsc ' . $argsString, array_merge((array)$cmdOptions, ['buffer' => true]));
    }
}