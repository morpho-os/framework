<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Php\Linting;

use Traversable;
use UnexpectedValueException;
use function array_merge;
use function basename;
use function count;
use function in_array;
use function Morpho\Base\init;
use function Morpho\Base\last;
use Morpho\Tech\Php\Reflection\ClassTypeDiscoverer;
use Morpho\Tech\Php\Reflection\FileReflection;
use Morpho\Fs\Path;
use function rtrim;
use function str_replace;
use function strlen;
use function strpos;
use function substr;

/*
@TODO
Add an option to fix the class name (if does not match and fix only first)
Add an option to fix namespace, (if does not match and fix only first)
*/
class FileChecker {
    public const META_FILE_NOT_FOUND = 'metaFileNotFound';
    public const INVALID_META_FILE_FORMAT = 'invalidMetaFileFormat';
    public const NS_NOT_FOUND = 'nsNotFound';
    public const INVALID_CLASS = 'invalidClass';
    public const INVALID_NS = 'invalidNs';

    public function checkFile(SourceFile $sourceFile): array {
        $errors = [];
        $errors = array_merge($errors, $this->checkNamespaces($sourceFile));
        $errors = array_merge($errors, $this->checkClassTypes($sourceFile));
        return count($errors) ? [$sourceFile->filePath() => $errors] : [];
    }

    public function checkNamespaces(SourceFile $sourceFile): array {
        $expectedNss = [];
        foreach ($sourceFile->nsToDirPathMap() as $nsPrefix => $libDirPath) {
            if (!Path::isAbs($libDirPath)) {
                $pos = strpos($libDirPath, '://'); // URI like vfs:///foo
                if (false !== $pos) {
                    $isAbs = isset($libDirPath[$pos + 3]) && $libDirPath[$pos + 3] === '/';
                } else {
                    $isAbs = false;
                }
                if (!$isAbs) {
                    throw new UnexpectedValueException('The library directory path must be absolute');
                }
            }
            if (str_starts_with($sourceFile->filePath(), $libDirPath)) {
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
        foreach (self::namespaces($sourceFile->filePath()) as $nsName) {
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
        $expectedClassName = Path::dropExt(basename($filePath));
        foreach (self::classes($sourceFile->filePath()) as $className) {
            $shortClassName = last($className, '\\');
            if ($shortClassName !== $expectedClassName) {
                $errors[self::INVALID_CLASS] = $className;
            }
            // We are checking only first class.
            return $errors;
        }
        return $errors;
    }

    /**
     * @param string $filePath
     * @return Traversable|string[]
     */
    protected function namespaces(string $filePath): iterable {
        $rFile = new FileReflection($filePath);
        foreach ($rFile->namespaces() as $rNamespace) {
            yield $rNamespace->name();
        }
    }

    /**
     * @param string $filePath
     * @return Traversable|string[]
     */
    protected function classes(string $filePath): iterable {
        $classTypeDiscoverer = new ClassTypeDiscoverer();
        $classes = $classTypeDiscoverer->classTypesDefinedInFile($filePath);
        return $classes;
    }
}
