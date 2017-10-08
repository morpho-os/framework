<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Core;

use Morpho\Core\Module;
use Morpho\Core\ModuleFs;
use Morpho\Core\ModuleInstaller;
use Morpho\Core\ModuleManager;
use Morpho\Db\Sql\Db;
use Morpho\Db\Sql\SchemaManager;
use Morpho\Test\DbTestCase;

class ModuleInstallerTest extends DbTestCase {
    public function testInstallModule() {
        $moduleInstaller = new ModuleInstaller();
        $moduleName = 'foo/bar';
        $moduleManager = $this->createMock(ModuleManager::class);
        $this->expectSchemaManagerMethod('createTables', ['test' => 123], $moduleInstaller, $moduleName, $moduleManager);

        $moduleInstaller->installModule($moduleName, $moduleManager);
    }

    public function testUninstallModule() {
        $moduleInstaller = new ModuleInstaller();
        $moduleName = 'shelf/book';
        $moduleManager = $this->createMock(ModuleManager::class);

        $this->expectSchemaManagerMethod('deleteTables', ['test'], $moduleInstaller, $moduleName, $moduleManager);

        $moduleInstaller->uninstallModule($moduleName, $moduleManager);
    }

    private function expectSchemaManagerMethod(string $method, $withValue, $moduleInstaller, string $moduleName, $moduleManager): void {
        $fs = $this->createConfiguredMock(ModuleFs::class, [
            'rcDirPath' => $this->getTestDirPath(),
        ]);
        $module = $this->createConfiguredMock(Module::class, [
            'fs' => $fs
        ]);
        $moduleManager->expects($this->once())
            ->method('offsetGet')
            ->with($moduleName)
            ->will($this->returnValue($module));
        $schemaManager = $this->createMock(SchemaManager::class);
        $schemaManager->expects($this->once())
            ->method($method)
            ->with($this->equalTo($withValue));
        $db = $this->createConfiguredMock(Db::class, [
            'schemaManager' => $schemaManager,
        ]);
        $moduleInstaller->setDb($db);
    }
}