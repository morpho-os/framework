<?php
namespace MorphoTest\Code;

use Morpho\Test\TestCase;
use Morpho\Code\ClassTypeDiscoverer;

class ClassTypeDiscovererTest extends TestCase {
    private $classTypeDiscoverer;

    public function setUp() {
        $this->classTypeDiscoverer = new ClassTypeDiscoverer();
    }

    public function testClassTypesDefinedInDir_UsingDefaultStrategy() {
        $this->assertEquals(str_replace('\\', '/', __FILE__), $this->classTypeDiscoverer->classTypesDefinedInDir(__DIR__)[__CLASS__]);
    }

    public function testDefaultStrategy() {
        $this->assertInstanceOf('\Morpho\Code\ClassTypeDiscoverer\TokenStrategy', $this->classTypeDiscoverer->discoverStrategy());
    }

    public function testClassTypesDefinedInDir_UsingCustomStrategy() {
        $discoverStrategy = $this->createMock(ClassTypeDiscoverer::class . '\\IDiscoverStrategy');
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
        $this->expectException('ReflectionException', "Class $class does not exist");
        ClassTypeDiscoverer::classTypeFilePath($class);
    }

    public function testFileDependsFromClassTypes() {
        $classTypes = ClassTypeDiscoverer::fileDependsFromClassTypes($this->getTestDirPath() . '/ClassTypeDeps.php');
        $this->assertEquals([
            self::class . '\A_ClassExtends',
            self::class . '\B_ClassImplementsA',
            self::class . '\B_ClassImplementsB',
            self::class . '\C_ClassUsesTrait',
            self::class . '\D_InstantiatesNewObject',
            self::class . '\E_CallsMethodStatically',
            self::class . '\F_ReadsStaticProperty',
            self::class . '\G_WritesStaticProperty',
            self::class . '\H_CatchesExceptionA',
            self::class . '\H_CatchesExceptionB',
            self::class . '\I_AppliesInstanceOfOperator',
            self::class . '\J_ReadsClassConstant',
            self::class . '\K_MethodDefinitionHasParameterWithType',
            self::class . '\L_MethodDefinitionHasReturnType',
            self::class . '\M_FunctionDefinitionHasParameterWithType',
            self::class . '\N_FunctionDefinitionHasReturnType',
            self::class . '\O_ConstructorDefinitionHasParameterWithType',
            self::class . '\P_ExtendsInterfaceA',
            self::class . '\P_ExtendsInterfaceB',
            self::class . '\Q_TraitUsesTrait',
            self::class . '\R_AnonymousClassExtends',
            self::class . '\S_AnonymousClassImplementsA',
            self::class . '\S_AnonymousClassImplementsB',
            self::class . '\T_AnonymousFunctionDefinitionHasParameterWithType',
            self::class . '\U_AnonymousFunctionDefinitionHasReturnType',
        ], $classTypes);
    }

    public function testFileDependsFromClassTypes_WithoutStdClassesArg() {
        $this->assertEquals([self::class . '\ISome'], ClassTypeDiscoverer::fileDependsFromClassTypes($this->getTestDirPath() . '/ClassTypeDepsWithStdClasses.php'));
        $this->assertEquals(['ArrayObject', self::class . '\ISome'], ClassTypeDiscoverer::fileDependsFromClassTypes($this->getTestDirPath() . '/ClassTypeDepsWithStdClasses.php', false));
    }
}
