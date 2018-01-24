<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Linting;

use function Morpho\Core\baseDirPath;
use Morpho\Fs\File;

class SourceFile {
    /**
     * @var string
     */
    private $filePath;

    private $moduleDirPath;

    public function __construct(string $filePath) {
        $this->filePath = $filePath;
    }

    public function filePath(): string {
        return $this->filePath;
    }

    public function setModuleDirPath(string $dirPath): void {
        $this->moduleDirPath = $dirPath;
    }

    public function moduleDirPath(): string {
        if (null === $this->moduleDirPath) {
            $this->moduleDirPath = baseDirPath($this->filePath);
        }
        return $this->moduleDirPath;
    }
}

class FileChecker {
    public const META_FILE_NOT_FOUND = 'metaFileNotFound';
    public const INVALID_META_FILE_FORMAT = 'invalidMetaFileFormat';

    public function checkFile(SourceFile $sourceFile): array {
/*
@TODO
Check namespaces (if > 1, then first)
Check that class name corresponds to file (if > 1, then first)
Add an option to fix the class name (if not match and fix only first)
Add an option to fix namespace, (if not match and fix only first)
*/
/*        $moduleMeta['dirPath'] = $moduleDirPath;
        //$moduleMeta['paths'][
        //'metaFilePath' => $metaFilePath,
        //'moduleMeta' => $moduleMeta,*/

        $result = $this->checkMetaFile($sourceFile);
        if (!$result) {
            $result = $this->checkNamespaces($sourceFile);
            $result = array_merge($result, $this->checkClassTypes($sourceFile));
        }
        return $result;
    }

    public function checkMetaFile(SourceFile $sourceFile): array {
        $moduleDirPath = $sourceFile->moduleDirPath();
        $metaFilePath = $moduleDirPath . '/composer.json';
        if (!is_file($metaFilePath)) {
            $result[] = 'metaFileNotFound';
            return $result;
        }
        try {
            $moduleMeta = File::readJson($metaFilePath);
            $result = [];
            if (!isset($moduleMeta['autoload']['psr-4'])) {
                $result[] = self::INVALID_META_FILE_FORMAT;
                return $result;
            }
        } catch (\RuntimeException $e) {
            $result[] = self::INVALID_META_FILE_FORMAT;
        }
        return $result;
    }

    public function checkNamespaces(SourceFile $sourceFile): array {
        /*
        d($context);

        $nss = $moduleMeta['autoload']['psr-4'];

        $expectedNss = [];
        foreach ($nss as $ns => $relLibDirPath) {
            $libDirPath = Path::combine($moduleDirPath, $relLibDirPath);
            if (startsWith($sourceFile->filePath(), $libDirPath)) {
                $ns = rtrim($ns, '\\');
                $expectedNss[] = init($ns . '\\' . str_replace('/', '\\', substr($sourceFile->filePath(), strlen($libDirPath) + 1)), '\\');
            }
        }
        if (!count($expectedNss)) {
            $result[] = ['invalidMetaFile' => $metaFilePath];
            return $result;
        }

        $rFile = new ReflectionFile($sourceFile->filePath());

        foreach ($rFile->namespaces() as $rNamespace) {
            $nsName = $rNamespace->name();

            // null means global
            if (null === $nsName || in_array($nsName, $expectedNss)) {
                $result['validNs'][] = $nsName;
            } else {
                $result['invalidNs'][] = $nsName;
            }
        }

        return $result;
        */
    }

    public function checkClassTypes(SourceFile $sourceFile): array {

    }
}