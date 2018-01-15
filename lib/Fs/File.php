<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Fs;

use Morpho\Base\Config;
use Morpho\Base\Environment;
use Morpho\Base\NotImplementedException;
use function Morpho\Base\{
    fromJson, toJson
};
use Morpho\Code\Code;

class File extends Entry {
    /**
     * Reads file as string.
     */
    public static function read(string $filePath, array $config = null): string {
        if (!is_file($filePath)) {
            throw new FileNotFoundException($filePath);
        }

        $config = Config::check(
            (array)$config,
            [
                'lock'           => false,
                'offset'         => -1,
                'length'         => null,
                'useIncludePath' => false,
                'context'        => null,
                'binary'         => true,
                'removeBom'      => true,
            ]
        );

        $content = @file_get_contents($filePath, $config['useIncludePath']);

        if (false === $content) {
            throw new Exception("Unable to read the '$filePath' file");
        }

        if ($config['binary']) {
            return $content;
        }

        // @TODO: Handle other BOM representations, see https://en.wikipedia.org/wiki/Byte_order_mark
        if ($config['removeBom'] && substr($content, 0, 3) === "\xEF\xBB\xBF") {
            return substr($content, 3);
        }

        return $content;
    }

    /**
     * NB: To read a file and then write to it use the construct: File::writeLines($filePath, toArray(File::readLines($filePath))
     */
    public static function writeLines(string $filePath, iterable $lines): string {
        if (is_array($lines)) {
            return self::write($filePath, implode("\n", $lines));
        }
        $handle = fopen($filePath, 'w');
        if (!$handle) {
            throw new Exception("Unable to open the '$filePath' file for writing");
        }
        try {
            foreach ($lines as $line) {
                fwrite($handle, $line . "\n");
            }
        } finally {
            fclose($handle);
        }
        return $filePath;
    }

    /**
     * @param null|\Closure|array $filterOrConfig
     */
    public static function readLines(string $filePath, $filterOrConfig = null, array $config = null): \Generator {
        if (is_array($filterOrConfig)) {
            if (is_array($config)) {
                throw new \InvalidArgumentException();
            }
            $config = $filterOrConfig;
            $filterOrConfig = null;
        }
        $defaultConfig = [
            'skipEmptyLines' => true,
            'rtrim' => true,
        ];
        if ($filterOrConfig) { // If a filter was specified, don't ignore empty lines.
            $defaultConfig['skipEmptyLines'] = false;
        }
        $config = Config::check((array) $config, $defaultConfig);
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Unable to open the '$filePath' file for reading");
        }
        try {
            while (false !== ($line = fgets($handle))) {
                if ($config['rtrim']) {
                    $line = rtrim($line);
                }
                if ($config['skipEmptyLines']) {
                    if (strlen($line) === 0) {
                        continue;
                    }
                }
                if (null !== $filterOrConfig) {
                    if ($filterOrConfig($line)) {
                        yield $line;
                    }
                } else {
                    yield $line;
                }
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * @return mixed Returns decoded json file's content.
     */
    public static function readJson(string $filePath) {
        return fromJson(self::read($filePath));
    }

    /**
     * Writes json to the file and returns the file path.
     */
    public static function writeJson(string $filePath, $json): string {
        return self::write($filePath, toJson($json));
    }

    public static function readCsv(string $filePath, string $delimiter = ',', string $enclosure = '"', string $escape = '\\'): \Generator {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Unable to read the '$filePath' file");
        }
        try {
            while (false !== ($line = fgetcsv($handle, 0, $delimiter, $enclosure, $escape))) {
                yield $line;
            }
        } finally {
            fclose($handle);
        }
    }

    public static function writeCsv(string $filePath): string {
        throw new NotImplementedException(__METHOD__);
    }

    public static function prepend(string $filePath, string $content, array $readConfig = null, array $writeConfig = null): string {
        $writeConfig['append'] = false;
        return self::write(
            $filePath,
            $content . self::read($filePath, $readConfig),
            $writeConfig
        );
    }

    /**
     * Appends content to the file and returns the file path.
     */
    public static function append(string $filePath, string $content, array $config = null): string {
        return self::write($filePath, $content, Config::check((array)$config, ['append' => true]));
    }

    /**
     * Writes string to file.
     */
    public static function write(string $filePath, string $content, array $config = null): string {
        if (empty($filePath)) {
            throw new Exception("The file path is empty");
        }
        Dir::create(dirname($filePath));
        $result = @file_put_contents($filePath, $content, static::filePutContentsConfigToFlags((array)$config), $config['context']);
        if (false === $result) {
            throw new Exception("Unable to write to the file '$filePath'");
        }

        return $filePath;
    }

    public static function writePhpVar(string $filePath, $var, bool $stripNumericKeys = true): string {
        File::write($filePath, '<?php return ' . Code::varToStr($var, $stripNumericKeys));
        return $filePath;
    }

    /**
     * Has the same effect as truncate but should be used in different situation/context.
     */
    public static function createEmpty(string $filePath): string {
        Dir::create(dirname($filePath));
        // NB: touch() does not truncate the file, so we don't use it.
        self::truncate($filePath);
        return $filePath;
    }

    /**
     * Truncates the file to zero length.
     */
    public static function truncate(string $filePath): void {
        $handle = fopen($filePath, 'w');
        if (false === $handle) {
            throw new Exception("Unable to open the file '$filePath' for writing");
        }
        fclose($handle);
    }
    
    public static function isEmpty(string $filePath): bool {
        clearstatcache();
        return filesize($filePath) === 0;
    }

    /**
     * Deletes the file.
     */
    public static function delete($filePath): void {
        if (is_iterable($filePath)) {
            foreach ($filePath as $path) {
                static::delete($path);
            }
            return;
        }
        if (!@unlink($filePath)) {
            throw new FileNotFoundException($filePath);
        }
    }
    
    public static function deleteIfExists(string $filePath): void {
        if (is_file($filePath)) {
            self::delete($filePath);
        }
    }

    /**
     * Copies the source file to target directory of file and returns target.
     */
    public static function copy(string $sourceFilePath, string $targetFilePath, bool $overwrite = false, bool $skipIfExists = false): string {
        if (!is_file($sourceFilePath)) {
            throw new Exception("Unable to copy: the source '$sourceFilePath' is not a file");
        }
        if (!is_dir(dirname($targetFilePath))) {
            Dir::create(dirname($targetFilePath));
        }
        if (is_dir($targetFilePath)) {
            $targetFilePath = $targetFilePath . '/' . basename($sourceFilePath);
        }
        if (is_file($targetFilePath) && !$overwrite) {
            if ($skipIfExists) {
                return $targetFilePath;
            } else {
                throw new Exception("The target file '$targetFilePath' already exists");
            }
        }
        if (!@copy($sourceFilePath, $targetFilePath)) {
            throw new Exception("Unable to copy the file '$sourceFilePath' to the '$targetFilePath'");
        }

        return $targetFilePath;
    }

    /**
     * @TODO: Add support directory for the $targetFilePath
     * Moves the source file to the target file and returns the target.
     */
    public static function move(string $sourceFilePath, string $targetFilePath): string {
        Dir::create(dirname($targetFilePath));
        if (!@rename($sourceFilePath, $targetFilePath)) {
            throw new Exception("Unable to move the '$sourceFilePath' to the '$targetFilePath'");
        }
        clearstatcache();

        return $targetFilePath;
    }

    public static function mustBeReadable(string $filePath): void {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new Exception("The file '$filePath' is not readable");
        }
    }

    public static function mustExist(string $filePath): string {
        if (empty($filePath)) {
            throw new Exception("The file path is empty");
        }
        if (!is_file($filePath)) {
            throw new Exception("The file does not exist");
        }
        return $filePath;
    }

    /**
     * @return mixed
     */
    public static function withTmp(callable $fn, string $tmpDirPath = null) {
        $tmpFilePath = tempnam($tmpDirPath ?: Environment::tmpDirPath(), __FUNCTION__);
        try {
            $res = $fn($tmpFilePath);
        } finally {
            if (is_file($tmpFilePath)) {
                unlink($tmpFilePath);
            }
        }
        return $res;
    }

    private static function filePutContentsConfigToFlags(array $config): int {
        $config = Config::check(
            $config,
            [
                'useIncludePath' => false,
                'lock'           => true,
                'append'         => false,
                'context'        => null,
                'mode'           => Stat::FILE_MODE,
            ]
        );
        $flags = 0;
        if ($config['append']) {
            $flags |= FILE_APPEND;
        }
        if ($config['lock']) {
            $flags |= LOCK_EX;
        }
        if ($config['useIncludePath']) {
            $flags |= FILE_USE_INCLUDE_PATH;
        }
        return $flags;
    }
}
