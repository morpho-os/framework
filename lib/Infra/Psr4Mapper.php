<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Infra;

class Psr4Mapper implements IPsr4Mapper {
    /**
     * @var string
     */
    protected $nsPrefix;

    /**
     * @var string
     */
    protected $baseDirPath;

    private $provideFilePaths;

    /**
     * @param $provideFilePaths: (ns: Namespace, path: Path): iterable
     */
    public function __construct(string $ns, string $baseDirPath, callable $provideFilePaths) {
        $this->nsPrefix = $ns;
        $this->baseDirPath = $baseDirPath;
        $this->provideFilePaths = $provideFilePaths;
    }

    public function nsPrefix(): string {
        return $this->nsPrefix;
    }

    public function baseDirPath(): string {
        return $this->baseDirPath;
    }

    public function filePaths(): iterable {
        return ($this->provideFilePaths)($this->nsPrefix, $this->baseDirPath);
    }
}