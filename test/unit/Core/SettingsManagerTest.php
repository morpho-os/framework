<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Core;

use Morpho\Base\ArrayTool;
use Morpho\Test\DbTestCase;
use Morpho\Core\SettingsManager;

class SettingsManagerTest extends DbTestCase {
    private $settingsManager;
    private $moduleName;

    public function setUp() {
        $db = $this->db();
        $moduleNames = ['module', 'setting'];
        $schemaManager = $db->schemaManager();
        $schemaManager->deleteAllTables();
        $schemaManager->createTables(ArrayTool::itemsWithKeys(\Morpho\System\Module::tableDefinitions(), $moduleNames));
        $this->settingsManager = new SettingsManager($db);
        $moduleName = 'foo';
        $db->insertRow('module', ['name' => $moduleName, 'status' => 1, 'weight' => 0]);
        $this->moduleName = $moduleName;
    }

    public function testSaveValueComplexType() {
        $moduleName = $this->moduleName;
        $this->assertFalse($this->settingsManager->get('foo', $moduleName));
        $instance = new \stdClass();
        $instance->prop = ['key' => 'val'];
        $this->settingsManager->set('foo', $instance, $moduleName);
        $actual = $this->settingsManager->get('foo', $moduleName);
        $this->assertEquals($instance, $actual);
    }

    public function testSet_Scalar() {
        $moduleName = $this->moduleName;
        $this->assertFalse($this->settingsManager->get('foo', $moduleName));
        $this->settingsManager->set('foo', 'bar', $moduleName);
        $this->assertEquals('bar', $this->settingsManager->get('foo', $moduleName));
    }
}
