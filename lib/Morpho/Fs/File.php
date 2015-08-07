<?php
namespace Morpho\Fs;

class File extends Entry {
    public static function read($filePath, array $options = array()) {
        $options += array(
            'lock' => false,
            'offset' => -1,
            'length' => null,
            'useIncludePath' => false,
            'context' => null,
            'binary' => true,
        );
        if (!is_file($filePath)) {
            throw new FileNotFoundException($filePath);
        }
        // @TODO: Add handling of $options.
        $content = @file_get_contents($filePath, $options['useIncludePath']);
        if (false === $content) {
            throw new IoException("Unable to read the '$filePath' file.");
        }
        if ($options['binary']) {
            return $content;
        }
        // Handle BOM.
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            return substr($content, 3);
        }

        return $content;
    }

    public static function readAsArray($filePath) {
        return file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    /**
     * Shortcut for the write() method that appends $content to the file.
     */
    public static function append($filePath, $content, array $options) {
        $options['append'] = true;
        self::write($filePath, $content, $options);
    }

    /**
     * @return $filePath
     */
    public static function write($filePath, $content, array $options = array()) {
        if (empty($filePath)) {
            throw new IoException("The file path is empty.");
        }
        $options += array(
            'useIncludePath' => false,
            'lock' => true,
            'append' => false,
            'context' => null,
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
        Directory::create(dirname($filePath));
        $result = @file_put_contents($filePath, $content, $flags, $options['context']);
        if (!$result) {
            throw new IoException("Unable to write to the file '$filePath'.");
        }

        return $filePath;
    }

    public static function truncate($filePath) {
        $handle = @fopen($filePath, 'w');
        if (false === $handle) {
            throw new IoException("Unable to open the file '$filePath' for writing.");
        }
        fclose($handle);
    }

    public static function delete($filePath) {
        if (!@unlink($filePath)) {
            throw new FileNotFoundException("Unable to delete the file '$filePath.'");
        }
    }

    /**
     * @param string $sourceFilePath
     * @param string $targetFilePath
     * @param bool $overwrite
     * @param bool $skipIfExists
     * @return string A new path of the copied file.
     */
    public static function copy($sourceFilePath, $targetFilePath, $overwrite = false, $skipIfExists = false) {
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
     * @param $sourceFilePath
     * @param $targetFilePath
     * @return string A new destination of moved file path.
     * @throws IoException
     */
    public static function move($sourceFilePath, $targetFilePath) {
        Directory::create(dirname($targetFilePath));
        if (!@rename($sourceFilePath, $targetFilePath)) {
            throw new IoException("Unable to move the '$sourceFilePath' to the '$targetFilePath'.");
        }
        clearstatcache();

        return $targetFilePath;
    }

    public static function uniquePath($filePath, $numberAttemps = 10000) {
        $uniquePath = $filePath;
        for ($i = 0; is_file($uniquePath) && $i < $numberAttemps; $i++) {
            $uniquePath = $filePath . '-' . $i;
        }
        if ($i == $numberAttemps && is_file($uniquePath)) {
            throw new IoException("Unable to generate unique path for file '$filePath' (tried $i times).");
        }

        return $uniquePath;
    }
}
