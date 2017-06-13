<?php
declare(strict_types = 1);
namespace Morpho\Fs;

use Morpho\Base\ArrayTool;
use Morpho\Base\NotImplementedException;
use function Morpho\Base\{
    fromJson, toJson
};
use Morpho\Code\CodeTool;

class File extends Entry {
    /**
     * Reads file as string.
     */
    public static function read(string $filePath, array $options = null): string {
        if (!is_file($filePath)) {
            throw new FileNotFoundException($filePath);
        }

        $options = ArrayTool::handleOptions(
            (array)$options,
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

        $content = @file_get_contents($filePath, $options['useIncludePath']);

        if (false === $content) {
            throw new Exception("Unable to read the '$filePath' file.");
        }

        if ($options['binary']) {
            return $content;
        }

        // Handle BOM.
        if ($options['removeBom'] && substr($content, 0, 3) === "\xEF\xBB\xBF") {
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
        foreach ($lines as $line) {
            fwrite($handle, $line . "\n");
        }
        fclose($handle);
        return $filePath;
    }

    public static function readLines(string $filePath, $filterOrOptions = null, array $options = null): \Generator {
        if (is_array($filterOrOptions)) {
            if (is_array($options)) {
                throw new \InvalidArgumentException();
            }
            $options = $filterOrOptions;
            $filterOrOptions = null;
        }
        $defaultOptions = [
            'skipEmptyLines' => true,
            'rtrim' => true,
        ];
        if ($filterOrOptions) { // If a filter was specified, don't ignore empty lines.
            $defaultOptions['skipEmptyLines'] = false;
        }
        $options = ArrayTool::handleOptions((array) $options, $defaultOptions);
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Unable to read the '$filePath' file");
        }
        while (false !== ($line = fgets($handle))) {
            if ($options['rtrim']) {
                $line = rtrim($line);
            }
            if ($options['skipEmptyLines']) {
                if (strlen($line) === 0) {
                    continue;
                }
            }
            if (null !== $filterOrOptions) {
                if ($filterOrOptions($line)) {
                    yield $line;
                }
            } else {
                yield $line;
            }
        }
        fclose($handle);
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
        $handle = fopen($filePath, "r");
        if (!$handle) {
            throw new Exception("Unable to read the '$filePath' file");
        }
        while (false !== ($line = fgetcsv($handle, 0, $delimiter, $enclosure, $escape))) {
            yield $line;
        }
        fclose($handle);
    }

    public static function writeCsv(string $filePath): string {
        throw new NotImplementedException(__METHOD__);
    }

    public static function prepend(string $filePath, string $content, array $readOptions = null, array $writeOptions = null): string {
        $writeOptions['append'] = false;
        return self::write(
            $filePath,
            $content . self::read($filePath, $readOptions),
            $writeOptions
        );
    }

    /**
     * Appends content to the file and returns the file path.
     */
    public static function append(string $filePath, string $content, array $options = null): string {
        return self::write($filePath, $content, ArrayTool::handleOptions((array)$options, ['append' => true]));
    }

    /**
     * Writes string to file.
     */
    public static function write(string $filePath, string $content, array $options = null): string {
        if (empty($filePath)) {
            throw new Exception("The file path is empty.");
        }
        Directory::create(dirname($filePath));
        $result = @file_put_contents($filePath, $content, static::filePutContentsOptionsToFlags((array)$options), $options['context']);
        if (false === $result) {
            throw new Exception("Unable to write to the file '$filePath'.");
        }

        return $filePath;
    }

    public static function writePhpVar(string $filePath, $var, bool $stripNumericKeys = true): string {
        File::write($filePath, '<?php return ' . CodeTool::varToString($var, $stripNumericKeys));
        return $filePath;
    }

    /**
     * Has the same effect as truncate but should be used in different situation/context.
     */
    public static function createEmpty(string $filePath): string {
        Directory::create(dirname($filePath));
        // @TODO: Why not touch()?
        self::truncate($filePath);
        return $filePath;
    }

    /**
     * Truncates the file to zero length.
     */
    public static function truncate(string $filePath): void {
        $handle = @fopen($filePath, 'w');
        if (false === $handle) {
            throw new Exception("Unable to open the file '$filePath' for writing.");
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
            Directory::create(dirname($targetFilePath));
        }
        if (is_dir($targetFilePath)) {
            $targetFilePath = $targetFilePath . '/' . basename($sourceFilePath);
        }
        if (is_file($targetFilePath) && !$overwrite) {
            if ($skipIfExists) {
                return $targetFilePath;
            } else {
                throw new Exception("The target file '$targetFilePath' already exists.");
            }
        }
        if (!@copy($sourceFilePath, $targetFilePath)) {
            throw new Exception("Unable to copy the file '$sourceFilePath' to the '$targetFilePath'.");
        }

        return $targetFilePath;
    }

    /**
     * @TODO: Add support directory for the $targetFilePath
     * Moves the source file to the target file and returns the target.
     */
    public static function move(string $sourceFilePath, string $targetFilePath): string {
        Directory::create(dirname($targetFilePath));
        if (!@rename($sourceFilePath, $targetFilePath)) {
            throw new Exception("Unable to move the '$sourceFilePath' to the '$targetFilePath'.");
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
        $tmpFilePath = tempnam($tmpDirPath ?: Directory::tmpPath(), __FUNCTION__);
        try {
            $res = $fn($tmpFilePath);
        } finally {
            if (is_file($tmpFilePath)) {
                unlink($tmpFilePath);
            }
        }
        return $res;
    }

    private static function filePutContentsOptionsToFlags(array $options): int {
        $options = ArrayTool::handleOptions(
            $options,
            [
                'useIncludePath' => false,
                'lock'           => true,
                'append'         => false,
                'context'        => null,
                'mode'           => Stat::FILE_MODE,
            ]
        );
        $flags = 0;
        if ($options['append']) {
            $flags |= FILE_APPEND;
        }
        if ($options['lock']) {
            $flags |= LOCK_EX;
        }
        if ($options['useIncludePath']) {
            $flags |= FILE_USE_INCLUDE_PATH;
        }
        return $flags;
    }
}