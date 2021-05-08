<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Php\Linting;

use Morpho\Base\Err;
use Morpho\Base\Ok;
use Morpho\Base\Result;
use Morpho\Fs\Path;
use Morpho\Tech\Php\Reflection\ClassTypeDiscoverer;
use Morpho\Tech\Php\Reflection\FileReflection;
use Traversable;

use function Morpho\Base\init;
use function Morpho\Base\last;

class FileChecker implements ILinter {
    public const INVALID_CLASS = 'invalidClass';
    public const INVALID_NS = 'invalidNs';

    public function check(mixed $context): Result {
        $invalidNss = $this->checkNamespaces($context);
        $invalidClassTypes = $this->checkClassTypes($context);
        $errors = [];
        if ($invalidNss) {
            $errors[self::INVALID_NS] = $invalidNss;
        }
        if ($invalidClassTypes) {
            $errors[self::INVALID_CLASS] = $invalidClassTypes;
        }
        return $errors ? new Err($errors) : new Ok();
    }

    public function checkNamespaces($context): array {
        $relPath = Path::rel($context['filePath'], $context['baseDirPath']);
        $expectedNs = rtrim($context['ns'], '\\');
        $nsSuffix = init(str_replace('/', '\\', $relPath), '\\');
        if ($nsSuffix !== '') {
            $expectedNs .= '\\' . $nsSuffix;
        }
        $allowGlobalNs = ctype_lower(basename($relPath)); // Allow only if filename starts with [a-z]
        foreach (self::namespaces($context['filePath']) as $ns) {
            if (null === $ns && $allowGlobalNs) {
                // null means global
                continue;
            }
            // We are checking only the first namespace.
            if ($ns !== $expectedNs) {
                return [
                    'expected' => $expectedNs,
                    'actual'   => $ns,
                ];
            }
            return [];
        }
        return [];
    }

    public function checkClassTypes($context): array {
        $filePath = $context['filePath'];
        $expectedClassName = Path::dropExt(basename($filePath));
        foreach (self::classes($filePath) as $className) {
            $shortClassName = last($className, '\\');
            if ($shortClassName !== $expectedClassName) {
                return [
                    'expected' => $expectedClassName,
                    'actual'   => $shortClassName,
                ];
            }
            // We are checking only the first class.
            return [];
        }
        return [];
    }

    /**
     * @param string $filePath
     * @return \Traversable|string[]
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
        return (new ClassTypeDiscoverer())->classTypesDefinedInFile($filePath);
    }
}
