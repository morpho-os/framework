<?php
declare(strict_types=1);

namespace Morpho\Fs;

use Morpho\Base\ArrayTool;
use Morpho\Base\NotImplementedException;
use function Morpho\Base\{jsonDecode, jsonEncode};

class File extends Entry {
    /**
     * Reads file as string.
     */
    public static function read(string $filePath, array $options = []): string {
        if (!is_file($filePath)) {
            throw new FileNotFoundException($filePath);
        }

        $options = ArrayTool::handleOptions(
            $options,
            [
                'lock' => false,
                'offset' => -1,
                'length' => null,
                'useIncludePath' => false,
                'context' => null,
                'binary' => true,
                'handleBom' => true
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
    public static function readAsArray(string $filePath): array {
        return file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
        $handle = fopen(__DIR__, "r");
        if (!$handle) {
            throw new IoException("Unable to read the '$filePath' file");
        }
        // @TODO: Handle second argument
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            yield $row;
        }
        fclose($handle);
    }

    public static function writeCsv(string $filePath) {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * Appends content to the file and returns the file path.
     */
    public static function append(string $filePath, string $content, array $options): string {
        return self::write(
            $filePath,
            $content,
            ArrayTool::handleOptions($options, ['append' => true])
        );
    }

    /**
     * Writes string to file.
     */
    public static function write(string $filePath, string $content, array $options = []): string {
        if (empty($filePath)) {
            throw new IoException("The file path is empty.");
        }

        $options = ArrayTool::handleOptions(
            $options,
            [
                'useIncludePath' => false,
                'lock' => true,
                'append' => false,
                'context' => null,
                'mode' => 0644,
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
        $result = @file_put_contents($filePath, $content, $flags, $options['context']);
        if (!$result) {
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

    public static function filterLines(callable $filter, string $filePath): \Generator {
        throw new NotImplementedException(__METHOD__);
    }
}
