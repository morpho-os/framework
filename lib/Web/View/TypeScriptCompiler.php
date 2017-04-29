<?php
namespace Morpho\Web\View;

use Morpho\Base\NotImplementedException;
use function Morpho\Base\trimMore;
use function Morpho\Cli\argsString;
use function Morpho\Cli\cmd;
use Morpho\Cli\CommandResult;
use function Morpho\Cli\escapeArgs;
use Morpho\Fs\File;

class TypeScriptCompiler {
    // Possible values: 'commonjs', 'amd', 'system', 'umd' or 'es2015'
    const MODULE_KIND = 'system';

    const TSCONFIG_FILE = 'tsconfig.json';

    // See https://www.typescriptlang.org/docs/handbook/compiler-options.html
    protected $options = [
        //'allowJs' => true,
        // @TODO: "allowSyntheticDefaultImports": true,
        'alwaysStrict' => true,
        'experimentalDecorators' => true,
        'forceConsistentCasingInFileNames' => true,
        'jsx' => 'preserve',
        'module' => self::MODULE_KIND,
        'moduleResolution' => 'node',
        'newLine' => 'LF',
        'noEmitOnError' => true,
        'noImplicitAny' => true,
        'noImplicitReturns' => true,
        'noImplicitThis' => true,
        'noUnusedLocals' => true,
        'preserveConstEnums' => true,
        'pretty' => true,
        'removeComments' => true,
        'strictNullChecks' => false,
    ];

    /**
     * @param string|iterable $inFilePath
     */
    public function compileToFile($inFilePath, string $outFilePath = null, array $cmdOptions = null): CommandResult {
        $options = [];
        if ($inFilePath) {
            $options = array_merge($options, (array)$inFilePath);
        }
        if ($outFilePath) {
            $options['outFile'] = $outFilePath;
        }
        $optionsStr = $this->optionsString($options);
        return $this->tsc($optionsStr, $cmdOptions);
    }

    public function compileToDir(string $inFilePath, string $outDirPath = null): CommandResult {
        $options = $this->escapeOptions(
            array_merge(
                $this->options(),
                [
                    'outDir' => $outDirPath,
                    $inFilePath,
                ]
            )
        );
        return $this->tsc(implode(' ', $options));
    }

    public function writeTsconfig(string $dirPath, array $config = null): string {
        // see http://json.schemastore.org/tsconfig for schema and the https://www.typescriptlang.org/docs/handbook/tsconfig-json.html for description.
        return File::writeJson(
            $dirPath . '/' . self::TSCONFIG_FILE,
            array_merge_recursive(
                [
                    'compilerOptions' => $this->options(),
                    'exclude' => [
                        '**/*.js',
                    ],
                ],
                (array)$config
            )
        );
    }

    public function version(): string {
        return (string)$this->tsc('--version');
    }

    public function help(): string {
        return (string)$this->tsc('--help');
    }

    public function possibleValuesOfOption(string $optionName): array {
        // @TODO: Use JSON schema file.
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

    public function option(string $name) {
        return $this->options[$name];
    }

    public function setOptions(array $options) {
        $this->options = $options;
    }

    public function options(): array {
        return $this->options;
    }

    public function optionsString(array $options = null): string {
        return implode(' ', $this->escapeOptions(array_merge($this->options(), (array) $options)));
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
            } elseif (is_array($value)) {
                $safe[] = escapeshellarg('--' . $name) . $sep . escapeshellarg(implode(',', $value));
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