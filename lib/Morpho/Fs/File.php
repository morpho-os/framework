<?php
declare(strict_types=1);

namespace Morpho\Fs;

use Morpho\Base\ArrayTool;

class File extends Entry {
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

    public static function readAsArray(string $filePath): array {
        return file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    /**
     * Shortcut for the write() method that appends $content to the file.
     */
    public static function append(string $filePath, string $content, array $options) {
        $options = ArrayTool::handleOptions($options, ['append' => true]);
        self::write($filePath, $content, $options);
    }

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
                'dirMode' => 0755,
            ]
        );

        Directory::create(dirname($filePath), $options['dirMode']);

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

        chmod($filePath, $options['mode']);

        return $filePath;
    }

    public static function truncate(string $filePath) {
        $handle = @fopen($filePath, 'w');
        if (false === $handle) {
            throw new IoException("Unable to open the file '$filePath' for writing.");
        }
        fclose($handle);
    }

    public static function delete(string $filePath) {
        if (!@unlink($filePath)) {
            throw new FileNotFoundException("Unable to delete the file '$filePath.'");
        }
    }

    public static function copy(string $sourceFilePath, string $targetFilePath, bool $overwrite = false, bool $skipIfExists = false): string {
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

    public static function move(string $sourceFilePath, string $targetFilePath): string {
        Directory::create(dirname($targetFilePath));
        if (!@rename($sourceFilePath, $targetFilePath)) {
            throw new IoException("Unable to move the '$sourceFilePath' to the '$targetFilePath'.");
        }
        clearstatcache();

        return $targetFilePath;
    }

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
}
