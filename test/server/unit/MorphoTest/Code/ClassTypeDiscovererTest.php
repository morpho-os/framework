<?php
namespace MorphoTest\Code;

use Morpho\Test\TestCase;
use Morpho\Code\ClassTypeDiscoverer;

class ClassTypeDiscovererTest extends TestCase {
    public function setUp() {
        $this->classTypeDiscoverer = new ClassTypeDiscoverer();
    }

    public function testClassTypesDefinedInDir_UsingDefaultStrategy() {
        $this->assertEquals(str_replace('\\', '/', __FILE__), $this->classTypeDiscoverer->classTypesDefinedInDir(__DIR__)[__CLASS__]);
    }

    public function testGetDefaultStrategy() {
        $this->assertInstanceOf('\Morpho\Code\ClassTypeDiscoverer\TokenStrategy', $this->classTypeDiscoverer->getDiscoverStrategy());
    }

    public function testClassTypesDefinedInDir_UsingCustomStrategy() {
        //$discoverStrategy = $this->getMock(ClassTypeDiscoverer::class . '\\IDiscoverStrategy');
        $discoverStrategy = $this->getMock('\\Morpho\\Code\\ClassTypeDiscoverer\\IDiscoverStrategy');
        $discoverStrategy->expects($this->atLeastOnce())
            ->method('classTypesDefinedInFile')
            ->will($this->returnValue([]));
        $this->assertInstanceOf(get_class($this->classTypeDiscoverer), $this->classTypeDiscoverer->setDiscoverStrategy($discoverStrategy));
        $this->classTypeDiscoverer->classTypesDefinedInDir(__DIR__);
    }

    public function dataForClassTestFilePath() {
        return [
            [
                self::class . '\\MyClass',
            ],
            [
                self::class . '\\IMyInterface',
            ],
            [
                self::class . '\\TMyTrait',
            ],
        ];
    }

    /**
     * @dataProvider dataForClassTestFilePath
     */
    public function testClassTypeFilePath(string $class) {
        $filePath = $this->getTestDirPath() . '/Test.php';
        require_once $filePath;
        $this->assertEquals($filePath, ClassTypeDiscoverer::classTypeFilePath($class));
    }

    public function testTypeFilePath_ThrowsExceptionOnNonExistingType() {
        $class = self::class . 'NonExisting';
        $this->setExpectedException('ReflectionException', "Class $class does not exist");
        ClassTypeDiscoverer::classTypeFilePath($class);
    }

    public function testFileDependsFromClassTypes() {
        $classTypes = ClassTypeDiscoverer::fileDependsFromClassTypes($this->getTestDirPath() . '/ClassTypeDeps.php');
        sort($classTypes);
        $this->assertEquals([
            self::class . '\A_Extends',
            self::class . '\B_Implements',
            self::class . '\C_Implements',
            self::class . '\D_Uses',
            self::class . '\E_Instantiates',
            self::class . '\F_CallsStatically',
            self::class . '\G_ReadsStaticProperty',
            self::class . '\H_WritesStaticProperty',
            self::class . '\I_CatchesException',
            self::class . '\J_CatchesException',
            self::class . '\K_AppliesInstanceOfOperator',
            self::class . '\L_ReadsClassConstant',
            self::class . '\M_ClassMethodDeclaresParameterWithType',
            self::class . '\N_ClassMethodDeclaresReturnType',
        ], $classTypes);
    }
}
