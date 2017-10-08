<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Db\Sql\Db;

interface IModuleInstaller {
    public function setDb(Db $db): void;

    public function installModule(string $moduleName, $moduleManager): void;

    public function uninstallModule(string $moduleName, $moduleManager): void;

    public function enableModule(string $moduleName, $moduleManager): void;

    public function disableModule(string $moduleName, $moduleManager): void;
}