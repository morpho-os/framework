<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Js;

use Morpho\Base\ArrayTool;
use Morpho\Base\Config;
use Morpho\Base\IFn;
use Morpho\Base\NotImplementedException;
use function Morpho\Base\trimMore;
use Morpho\Cli\ICommandResult;
use function Morpho\Cli\shell;
use Morpho\Fs\File;

class TsCompiler implements IFn {
    public const NONE_MODULE_KIND = 'none';
    public const COMMONJS_MODULE_KIND = 'commonjs';
    public const AMD_MODULE_KIND = 'amd';
    public const SYSTEM_MODULE_KIND = 'system';
    public const UMD_MODULE_KIND = 'umd';
    public const ES2015_MODULE_KIND = 'es2015';
    public const ESNEXT_MODULE_KIND = 'ESNext';

    private const TSCONFIG_FILE = 'tsconfig.json';

    /**
     * @var Config
     */
    protected $shellConfig = [];
    /**
     * @var TscConfig
     */
    private $compilerConfig;

    public function __construct() {
        $this->compilerConfig = new TscConfig();
        $this->shellConfig = new Config();
    }

    /**
     * @param array|Config $config
     */
    public function __invoke($config): ICommandResult {
        $config = ArrayTool::handleConfig($config, [
            'compilerConfig' => $this->compilerConfig(),
            'shellConfig' => $this->shellConfig(),
        ]);
        return $this->tsc($config['compilerConfig'], $config['shellConfig']);
    }

    public function compilerConfig(): TscConfig {
        return $this->compilerConfig;
    }

    public function shellConfig(): Config {
        return $this->shellConfig;
    }

    /**
     * @param string|iterable $inFilePath
     * @param null|array|Config $shellConfig
     */
    public function compileToFile($inFilePath, string $outFilePath = null, $shellConfig = null): ICommandResult {
        $compilerConfig = $this->compilerConfig()->getArrayCopy();
        if ($inFilePath) {
            $compilerConfig = array_merge($compilerConfig, (array)$inFilePath);
        }
        if ($outFilePath) {
            $compilerConfig['outFile'] = $outFilePath;
        }
        return $this->tsc($compilerConfig, $shellConfig);
    }

    /**
     * @param null|array|Config $shellConfig
     */
    public function compileToDir(string $inFilePath, string $outDirPath = null, $shellConfig = null): ICommandResult {
        $compilerConfig = $this->compilerConfig()->getArrayCopy();
        $compilerConfig['outDir'] = $outDirPath;
        $compilerConfig[] = $inFilePath;
        return $this->tsc($compilerConfig, $shellConfig);
    }

    /**
     * @param string $dirPath
     * @param Config|array|null $config
     * @return string
     */
    public function writeTsconfigFile(string $dirPath, $config = null): string {
        // Schema: http://json.schemastore.org/tsconfig
        // Description: https://www.typescriptlang.org/docs/handbook/tsconfig-json.html
        return File::writeJson(
            $dirPath . '/' . self::TSCONFIG_FILE,
            $config ? (array) $config : ['compilerOptions' => (array)$this->compilerConfig()]
        );
    }

    public function version(): string {
        $versionStr = preg_replace('~^Version\s+~si', '', trim((string)$this->tsc(['--version'])));
        return $versionStr;
    }

    public function valuesOfCompilerConfigParam(string $paramName): array {
        // @TODO: Use JSON schema file, http://json.schemastore.org/tsconfig
        $help = function () {

        };
        switch ($paramName) {
            case 'module':
                if (!preg_match('~^.*--module\s+KIND\s+(.*)$~m', $help(), $match) || !preg_match_all("~('[^']+')~s", $match[1], $match)) {

                    throw new \RuntimeException("Unable to parse help");
                }
                return trimMore($match[1], "'");
            default:
                throw new NotImplementedException();
        }
    }

    /**
     * @param null|array|Config $config
     * @return string
     */
    public function compilerConfigStr($config = null): string {
        if ($config) {
            $compilerConfig = clone $this->compilerConfig();
            $compilerConfig->merge($config);
        } else {
            $compilerConfig = $this->compilerConfig();
        }
        return $this->configToArgsStr($compilerConfig);
    }

    /**
     * @param Config|array $compilerConfig
     * @param Config|array|null $shellConfig
     * @return ICommandResult
     */
    public function tsc($compilerConfig, $shellConfig = null): ICommandResult {
        return shell(
            'tsc ' . $this->configToArgsStr($compilerConfig),
            array_merge((array)$shellConfig, ['capture' => true])
        );
    }

    /**
     * @param Config|array $config
     * @return string
     */
    protected function configToArgsStr($config): string {
        return implode(' ', $this->escapeConfig($config));
    }

    /**
     * @param Config|array $config
     * @return array
     */
    protected function escapeConfig($config): array {
        $safe = [];
        $sep = ' ';
        foreach ($config as $name => $value) {
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
}