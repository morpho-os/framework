<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Db\Sql\Db;

class ModuleInstaller implements IModuleInstaller {
    /**
     * @var Db
     */
    private $db;

    public function setDb(Db $db): void {
        $this->db = $db;
    }

    public function installModule(string $moduleName, $moduleManager): void {
        $schema = $this->schema($moduleName, $moduleManager);
        if (false === $schema) {
            return;
        }
        $this->db->schemaManager()->createTables($schema);
    }

    public function uninstallModule(string $moduleName, $moduleManager): void {
        $schema = $this->schema($moduleName, $moduleManager);
        if (false === $schema) {
            return;
        }
        $this->db->schemaManager()->deleteTables(array_keys($schema));
    }

    public function enableModule(string $moduleName, $moduleManager): void {
        // Do nothing here
    }

    public function disableModule(string $moduleName, $moduleManager): void {
        // Do nothing here
    }

    /**
     * @return array|false
     */
    private function schema(string $moduleName, $moduleManager) {
        $module = $moduleManager->offsetGet($moduleName);
        $schemaFilePath = $module->fs()->rcDirPath() . '/' . SCHEMA_FILE_NAME;
        if (!is_file($schemaFilePath)) {
            return false;
        }
        return require $schemaFilePath;
    }
}
