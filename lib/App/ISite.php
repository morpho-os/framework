<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

use Morpho\Base\IFn;

/**
 * site: {conf, module[]}
 */
interface ISite extends IFn {
    /**
     * Returns site's name.
     */
    public function name(): string;

    public function conf(): array;

    public function moduleName(): string;

    public function hostName(): ?string;

    public function moduleConf(string $moduleName): array;

    public function backendModuleDirPath(): iterable;
}
