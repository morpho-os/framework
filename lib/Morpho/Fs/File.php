<?php
declare(strict_types = 1);

namespace Morpho\Fs;

use Morpho\Base\ArrayTool;
use Morpho\Base\NotImplementedException;
use function Morpho\Base\{
    jsonDecode, jsonEncode
};

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
                'handleBom'      => true,
            ]
        );

        $content = @file_get_contents($filePath, $options['useIncludePath']);

        if (false === $content) {
            throw new IoException("Unable to read the '$filePath' file.");
        }

        if ($options['binary']) {
            return $content;
        }

        // Handle BOM.
        if ($options['handleBom'] && substr($content, 0, 3) === "\xEF\xBB\xBF") {
            return substr($content, 3);
        }

        return $content;
    }

    /**
     * Returns non empty lines from file as array.
     */
    public static function readArray(string $filePath) {
        return file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    public static function writeArray(string $filePath, array $arr): string {
        return self::write($filePath, implode("\n", $arr));
    }

    public static function readLines(string $filePath): \Generator {
        // @TODO: replace filterLines() with readLines() and filter()
        throw new NotImplementedException();
    }

    public static function filterLines(callable $filter, string $filePath): \Generator {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new IoException("Unable to read the '$filePath' file");
        }
        while (false !== ($line = fgets($handle))) {
            if ($filter($line)) {
                yield $line;
            }
        }
        fclose($handle);
    }

    /**
     * @return mixed Returns decoded json file's content.
     */
    public static function readJson(string $filePath) {
        return jsonDecode(self::read($filePath));
    }

    /**
     * Writes json to the file and returns the file path.
     */
    public static function writeJson(string $filePath, $json): string {
        return self::write($filePath, jsonEncode($json));
    }

    public static function readCsv(string $filePath): \Generator {
        // @TODO: Replace with readLines()?
        $handle = fopen($filePath, "r");
        if (!$handle) {
            throw new IoException("Unable to read the '$filePath' file");
        }
        // @TODO: Handle second argument
        while (false !== ($line = fgetcsv($handle, null, ','))) {
            yield $line;
        }
        fclose($handle);
    }

    public static function writeCsv(string $filePath) {
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
            throw new IoException("The file path is empty.");
        }
        $result = @file_put_contents($filePath, $content, static::filePutContentsOptionsToFlags((array)$options), $options['context']);
        if (false === $result) {
            throw new IoException("Unable to write to the file '$filePath'.");
        }

        return $filePath;
    }

    /**
     * Has the same effect as truncate but should be used in different situation/context.
     */
    public static function createEmpty(string $filePath) {
        self::truncate($filePath);
    }

    /**
     * Truncates the file to zero length.
     */
    public static function truncate(string $filePath) {
        $handle = @fopen($filePath, 'w');
        if (false === $handle) {
            throw new IoException("Unable to open the file '$filePath' for writing.");
        }
        fclose($handle);
    }

    /**
     * Deletes the file.
     */
    public static function delete(string $filePath) {
        if (!@unlink($filePath)) {
            throw new FileNotFoundException("Unable to delete the file '$filePath.'");
        }
    }

    /**
     * Copies the source file to target directory of file and returns target.
     */
    public static function copy(string $sourceFilePath, string $targetFilePath, bool $overwrite = false, bool $skipIfExists = false): string {
        if (!is_file($sourceFilePath)) {
            throw new IoException("Unable to copy: the source '$sourceFilePath' is not a file");
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
                throw new IoException("The target file '$targetFilePath' already exists.");
            }
        }
        if (!@copy($sourceFilePath, $targetFilePath)) {
            throw new IoException("Unable to copy the file '$sourceFilePath' to the '$targetFilePath'.");
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
            throw new IoException("Unable to move the '$sourceFilePath' to the '$targetFilePath'.");
        }
        clearstatcache();

        return $targetFilePath;
    }

    /**
     * Returns unique path for the file. Does not support concurrent access to the same directory.
     */
    public static function uniquePath(string $filePath, int $numberOfAttempts = 10000): string {
        $uniquePath = $filePath;
        for ($i = 0; is_file($uniquePath) && $i < $numberOfAttempts; $i++) {
            $uniquePath = $filePath . '-' . $i;
        }
        if ($i == $numberOfAttempts && is_file($uniquePath)) {
            throw new IoException("Unable to generate unique path for file '$filePath' (tried $i times).");
        }

        return $uniquePath;
    }

    protected static function filePutContentsOptionsToFlags(array $options): int {
        $options = ArrayTool::handleOptions(
            $options,
            [
                'useIncludePath' => false,
                'lock'           => true,
                'append'         => false,
                'context'        => null,
                'mode'           => 0644,
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
