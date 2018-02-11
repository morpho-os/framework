<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Infra;

abstract class Psr4MappingProvider implements IPsr4MappingProvider {
    /**
     * @var string
     */
    protected $nsPrefix;

    /**
     * @var string
     */
    protected $baseDirPath;

    public function __construct(string $ns, string $baseDirPath) {
        $this->nsPrefix = $ns;
        $this->baseDirPath = $baseDirPath;
    }

    public function nsPrefix(): string {
        return $this->nsPrefix;
    }

    public function baseDirPath(): string {
        return $this->baseDirPath;
    }
}