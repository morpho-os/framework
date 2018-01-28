<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Linting;

use function Morpho\Base\init;
use function Morpho\Base\last;
use function Morpho\Base\startsWith;
use Morpho\Code\Reflection\ReflectionFile;
use Morpho\Fs\File;
use Morpho\Fs\Path;

class FileChecker {
    public const META_FILE_NOT_FOUND = 'metaFileNotFound';
    public const INVALID_META_FILE_FORMAT = 'invalidMetaFileFormat';
    public const NS_NOT_FOUND = 'nsNotFound';
    public const INVALID_CLASS = 'invalidClass';
    public const INVALID_NS = 'invalidNs';

    public function checkFile(SourceFile $sourceFile): array {
/*
@TODO
Add an option to fix the class name (if not match and fix only first)
Add an option to fix namespace, (if not match and fix only first)
*/
        $errors = $this->checkMetaFile($sourceFile);
        if (!$errors) {
            $errors = $this->checkNamespaces($sourceFile);
            $errors = array_merge($errors, $this->checkClassTypes($sourceFile));
        }
        return $errors;
    }

    public function checkMetaFile(SourceFile $sourceFile): array {
        $moduleDirPath = $sourceFile->moduleDirPath();
        $metaFilePath = $moduleDirPath . '/composer.json';
        $errors = [];
        if (!is_file($metaFilePath)) {
            $errors[] = 'metaFileNotFound';
        } else {
            try {
                $moduleMeta = File::readJson($metaFilePath);
                if (!isset($moduleMeta['autoload']['psr-4'])) {
                    $errors[] = self::INVALID_META_FILE_FORMAT;
                } else {
                    $sourceFile['nsToDirPathMap'] = $moduleMeta['autoload']['psr-4'];
                }
            } catch (\RuntimeException $e) {
                $errors[] = self::INVALID_META_FILE_FORMAT;
            }
        }
        return $errors;
    }

    public function checkNamespaces(SourceFile $sourceFile): array {
        $expectedNss = [];
        $moduleDirPath = $sourceFile->moduleDirPath();
        foreach ($sourceFile['nsToDirPathMap'] as $nsPrefix => $relLibDirPath) {
            $libDirPath = Path::combine($moduleDirPath, $relLibDirPath);
            if (startsWith($sourceFile->filePath(), $libDirPath)) {
                $nsPrefix = rtrim($nsPrefix, '\\');
                $nsSuffix = str_replace('/', '\\', substr($sourceFile->filePath(), strlen($libDirPath) + 1));
                $ns = $nsPrefix . '\\' . $nsSuffix;
                $expectedNss[] = init($ns, '\\');
            }
        }
        $errors = [];
        if (!count($expectedNss)) {
            $errors[] = self::NS_NOT_FOUND;
            return $errors;
        }

        // Check namespaces (if > 1, then first)
        $rFile = new ReflectionFile($sourceFile->filePath());
        foreach ($rFile->namespaces() as $rNamespace) {
            $nsName = $rNamespace->name();

            if (null === $nsName) {
                // null means global
                continue;
            }

            if (!in_array($nsName, $expectedNss, true)) {
                $errors[self::INVALID_NS] = $nsName;
            }

            // We are checking only a first namespace.
            break;
        }

        return $errors;
    }

    public function checkClassTypes(SourceFile $sourceFile): array {
        $errors = [];
        $filePath = $sourceFile->filePath();
        $rFile = new ReflectionFile($filePath);
        $expectedClassName = Path::dropExt(basename($filePath));
        foreach ($rFile->namespaces() as $rNamespace) {
            foreach ($rNamespace->classTypes() as $rClass) {
                $className = $rClass->getName();
                /** @var \Morpho\Code\Reflection\ReflectionClass $rClass */
                $shortClassName = last($className, '\\');
                if ($shortClassName !== $expectedClassName) {
                    $errors[self::INVALID_CLASS] = $className;
                }
                // We are checking only first class.
                return $errors;
            }
        }
        return $errors;
    }
}