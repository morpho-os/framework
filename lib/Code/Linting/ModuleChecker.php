<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Linting;

use Morpho\Fs\File;

/*
@TODO
Add an option to fix the class name (if does not match and fix only first)
Add an option to fix namespace, (if does not match and fix only first)
*/
class ModuleChecker {
    public const META_FILE_NOT_FOUND = 'metaFileNotFound';
    public const INVALID_META_FILE_FORMAT = 'invalidMetaFileFormat';

    public static function checkMetaFile(string $metaFilePath): array {
        $errors = [];
        if (!is_file($metaFilePath)) {
            $errors[] = 'metaFileNotFound';
        } else {
            try {
                $moduleMeta = File::readJson($metaFilePath);
                if (!isset($moduleMeta['autoload']['psr-4'])) {
                    $errors[] = self::INVALID_META_FILE_FORMAT;
                }
            } catch (\RuntimeException $e) {
                $errors[] = self::INVALID_META_FILE_FORMAT;
            }
        }
        return $errors;
    }
}