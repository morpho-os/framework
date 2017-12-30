<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Js;

use Morpho\Base\NotImplementedException;
use function Morpho\Base\trimMore;
use Morpho\Cli\ICommandResult;
use function Morpho\Cli\shell;
use Morpho\Fs\File;
use Zend\Stdlib\ArrayUtils;

class TypeScriptCompiler extends Compiler {
    public const NONE_MODULE_KIND = 'none';
    public const COMMONJS_MODULE_KIND = 'commonjs';
    public const AMD_MODULE_KIND = 'amd';
    public const SYSTEM_MODULE_KIND = 'system';
    public const UMD_MODULE_KIND = 'umd';
    public const ES2015_MODULE_KIND = 'es2015';
    public const ESNEXT_MODULE_KIND = 'ESNext';
    // Default module kind
    public const MODULE_KIND = 'amd';

    private const TSCONFIG_FILE = 'tsconfig.json';

    // See https://www.typescriptlang.org/docs/handbook/compiler-options.html
    protected $options = [
        'allowJs' => true,
        // @TODO: "allowSyntheticDefaultImports": true,
        'alwaysStrict' => true,
        'experimentalDecorators' => true,
        'forceConsistentCasingInFileNames' => true,
        'jsx' => 'preserve',
        'module' => self::MODULE_KIND,
        'moduleResolution' => 'node',
        'newLine' => 'lf',
        'noEmitOnError' => true,
        'noImplicitAny' => true,
        'noImplicitReturns' => true,
        'noImplicitThis' => true,
        'noUnusedLocals' => false,
        'preserveConstEnums' => true,
        //'pretty' => true,
        'removeComments' => true,
        'sourceMap' => true,
        'strictNullChecks' => false,
        'target' => 'es5',
        "lib" => ["dom", "es2015.promise", "es2015.iterable", "es5"],
    ];

    private $pathEnvVar;

    public function compile($input): CompilationResult {
        $result = new CompilationResult();
        /** @var TscCompileOptions $input */
        $optionsStr = $this->optionsString($input->options());
        $cmdOptions = $input->cmdOptions();
        $result->append(
            $this->tsc($optionsStr, $cmdOptions)
        );
        return $result;
    }

    public function __invoke($input): CompilationResult {
        return $this->compile($input);
    }

    /**
     * @param string|iterable $inFilePath
     */
    public function compileToFile($inFilePath, string $outFilePath = null, array $cmdOptions = null): ICommandResult {
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

    public function compileToDir(string $inFilePath, string $outDirPath = null): ICommandResult {
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
        // Schema: http://json.schemastore.org/tsconfig
        // Description: https://www.typescriptlang.org/docs/handbook/tsconfig-json.html
        return File::writeJson(
            $dirPath . '/' . self::TSCONFIG_FILE,
            ArrayUtils::merge(
                [
                    'compilerOptions' => $this->options(),
                    /*
                    'exclude' => [
                        '** /*.js',
                    ],
                    */
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
        // @TODO: Use JSON schema file, http://json.schemastore.org/tsconfig
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

    public function setOption(string $name, $value): self {
        $this->options[$name] = $value;
        return $this;
    }

    public function option(string $name) {
        return $this->options[$name];
    }

    public function setOptions(array $options): self {
        $this->options = $options;
        return $this;
    }

    public function options(): array {
        return $this->options;
    }

    public function hasOption(string $name): bool {
        return array_key_exists($name, $this->options);
    }

    public function optionsString(array $options = null): string {
        return implode(' ', $this->escapeOptions(array_merge($this->options(), (array) $options)));
    }

    public function setPathEnvVar(string $value): self {
        $this->pathEnvVar = $value;
        return $this;
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

    protected function tsc(string $argsString, array $cmdOptions = null): ICommandResult {
        return shell(
            ($this->pathEnvVar ? 'PATH=' . escapeshellarg($this->pathEnvVar) . ' ' : '')
            . 'tsc '
            . $argsString,
            array_merge(
                (array)$cmdOptions, ['capture' => true]
            )
        );
    }
}