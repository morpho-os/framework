<?php
namespace MorphoTest\Core;

use Morpho\Test\DbTestCase;
use Morpho\Core\SettingManager;

class SettingManagerTest extends DbTestCase {
    public function setUp() {
        $db = $this->createDb();
        $db->dropTables(['module', 'setting']);
        $db->createTableForClasses([
            '\Morpho\Core\Module',
            '\Morpho\Core\Setting',
        ]);
        $this->settingManager = new SettingManager($db);
        $moduleName = 'foo';
        $db->insertRow('module', ['name' => $moduleName, 'status' => 1, 'weight' => 0]);
        $this->moduleName = $moduleName;
    }

    public function testSaveValueComplexType() {
        $moduleName = $this->moduleName;
        $this->assertFalse($this->settingManager->get('foo', $moduleName));
        $instance = new \stdClass();
        $instance->prop = ['key' => 'val'];
        $this->settingManager->set('foo', $instance, $moduleName);
        $actual = $this->settingManager->get('foo', $moduleName);
        $this->assertEquals($instance, $actual);
    }

    public function testSet_Scalar() {
        $moduleName = $this->moduleName;
        $this->assertFalse($this->settingManager->get('foo', $moduleName));
        $this->settingManager->set('foo', 'bar', $moduleName);
        $this->assertEquals('bar', $this->settingManager->get('foo', $moduleName));
    }
}
