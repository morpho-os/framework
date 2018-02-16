<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Infra;

interface IPsr4Mapper {
    /**
     * Returns namespace prefix
     */
    public function nsPrefix(): string;

    /**
     * Returns directory path prefix which must correspond to the namespace prefix.
     */
    public function baseDirPath(): string;

    /**
     * Yields paths of files which must be:
     *     - stored within directory with path returned by the baseDirPath()
     *     - and have namespace prefix returned by the nsPrefix().
     */
    public function filePaths(): iterable;
}