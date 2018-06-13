<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Code\Reflection;

use Morpho\Code\Reflection\ClassTypeReflection;
use Morpho\Code\Reflection\FileReflection;
use Morpho\Code\Reflection\FunctionReflection;
use Morpho\Code\Reflection\GlobalNamespaceReflection;
use Morpho\Code\Reflection\NamespaceReflection;
use Morpho\Testing\TestCase;

class FileReflectionTest extends TestCase {
    public function testClassUsingUnknownTrait() {
        $filePath = $this->getTestDirPath() . '/class-using-unknown-trait.php';
        $rFile = new FileReflection($filePath);
        $i = $j = 0;
        foreach ($rFile->namespaces() as $rNs) {
            $this->assertSame(__CLASS__, $rNs->name());
            $i++;
            /** @var NamespaceReflection $rNs */
            foreach ($rNs->classTypes() as $rClass) {
                $this->assertSame(__CLASS__ . '\\Test', $rClass->name());
                $j++;
            }
        }
        $this->assertSame(1, $i);
        $this->assertSame(1, $j);
    }

    public function testFilePath() {
        $filePath = $this->getTestDirPath() . '/empty-file.php';
        $rFile = new FileReflection($filePath);
        $this->assertEquals($filePath, $rFile->filePath());
    }

    public function testClasses() {
        $this->checkClasses('classes', 2, function ($i, ClassTypeReflection $rClass) {
            switch ($i) {
                case 0:
                    $this->assertSame(__CLASS__ . '\\ServiceManager', $rClass->name());
                    break;
                case 1:
                    $this->assertSame(__CLASS__ . '\\ServiceNotFoundException', $rClass->name());
                    break;
            }
        });
    }

    public function testTraits() {
        $this->checkClasses('traits', 1, function ($i, ClassTypeReflection $rClass) {
            switch ($i) {
                case 0:
                    $this->assertSame(__CLASS__ . '\\THasServiceManager', $rClass->name());
                    break;
            }
        });
    }

    public function testInterfaces() {
        $this->checkClasses('interfaces', 2, function ($i, ClassTypeReflection $rClass) {
            switch ($i) {
                case 0:
                    $this->assertSame(__CLASS__ . '\\IHasServiceManager', $rClass->name());
                    break;
                case 1:
                    $this->assertSame(__CLASS__ . '\\IServiceManager', $rClass->name());
                    break;
            }
        });
    }

    public function testNamespaces_EmptyFile() {
        $filePath = $this->getTestDirPath() . '/empty-file.php';
        $rFile = new FileReflection($filePath);
        $this->assertYields([], $rFile->namespaces());
    }

    public function testNamespaces_GlobalNamespace() {
        $filePath = $this->getTestDirPath() . '/global-ns.php';
        $rFile = new FileReflection($filePath);
        $i = 0;
        foreach ($rFile->namespaces() as $rNamespace) {
            $this->assertInstanceOf(NamespaceReflection::class, $rNamespace);
            $this->assertNull($rNamespace->name());
            $this->assertInstanceOf(GlobalNamespaceReflection::class, $rNamespace);
            $this->assertEquals($filePath, $rNamespace->filePath());

            $this->checkClassTypes(['Bar4f978258c3d87c0711d6f17c6b4ecfcd', 'TMoon4f978258c3d87c0711d6f17c6b4ecfcd'], $filePath, $rNamespace);
            $this->checkFunctions(['foo4f978258c3d87c0711d6f17c6b4ecfcd'], $filePath, $rNamespace);

            $i++;
        }
        $this->assertEquals(1, $i);
    }

    public function dataForNamespaces_MultipleNamespaces() {
        $testDirPath = $this->getTestDirPath();
        return [
            [
                $testDirPath . '/multiple-bracketed-ns.php',
                [
                    [
                        'classes' => [__CLASS__ . '_Ns1Bracketed\\First', __CLASS__ . '_Ns1Bracketed\\Second'],
                        'functions' => [__CLASS__ . '_Ns1Bracketed\\foo', __CLASS__ . '_Ns1Bracketed\\bar'],
                    ],
                    [
                        'classes' => [__CLASS__ . '_Ns2Bracketed\\Third', __CLASS__ . '_Ns2Bracketed\\TFourth'],
                        'functions' => [__CLASS__ . '_Ns2Bracketed\\baz'],
                    ],
                ],
            ],
            [
                $testDirPath . '/multiple-unbracketed-ns.php',
                [
                    [
                        'classes' => [__CLASS__ . '_Ns1Unbracketed\\First', __CLASS__ . '_Ns1Unbracketed\\Second'],
                        'functions' => [__CLASS__ . '_Ns1Unbracketed\\foo', __CLASS__ . '_Ns1Unbracketed\\bar'],
                    ],
                    [
                        'classes' => [__CLASS__ . '_Ns2Unbracketed\\Third', __CLASS__ . '_Ns2Unbracketed\\TFourth'],
                        'functions' => [__CLASS__ . '_Ns2Unbracketed\\baz'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataForNamespaces_MultipleNamespaces
     */
    public function testNamespaces_MultipleNamespaces($filePath, $expected) {
        $rFile = new FileReflection($filePath);
        $i = 0;
        foreach ($rFile->namespaces() as $rNamespace) {
            $this->assertInstanceOf(NamespaceReflection::class, $rNamespace);
            $this->assertNotInstanceOf(GlobalNamespaceReflection::class, $rNamespace);
            $this->assertEquals($filePath, $rNamespace->filePath());

            $this->checkClassTypes($expected[$i]['classes'], $filePath, $rNamespace);
            $this->checkFunctions($expected[$i]['functions'], $filePath, $rNamespace);

            $i++;
        }
        $this->assertEquals(2, $i);
    }

    private function assertYields($expected, \Traversable $gen) {
        $this->assertEquals($expected, \iterator_to_array($gen, false));
    }

    private function checkClassTypes(array $expectedClasses, string $filePath, NamespaceReflection $rNamespace) {
        $j = 0;
        foreach ($rNamespace->classTypes() as $rClass) {
            $this->checkClassTypeReflection($expectedClasses[$j], $filePath, $rClass);
            $j++;
        }
        $this->assertEquals(\count($expectedClasses), $j);
    }

    private function checkFunctions(array $expectedFns, string $filePath, NamespaceReflection $rNamespace) {
        $j = 0;
        foreach ($rNamespace->functions() as $rFunction) {
            $this->checkReflectionFunction($expectedFns[$j], $filePath, $rFunction);
            $j++;
        }
        $this->assertEquals(\count($expectedFns), $j);
    }

    private function checkClassTypeReflection(string $expectedClass, string $expectedFilePath, ClassTypeReflection $rClass) {
        $this->assertEquals($expectedClass, $rClass->name());
        $this->assertEquals($expectedFilePath, $rClass->filePath());
    }

    private function checkReflectionFunction(string $expectedFnName, string $expectedFilePath, FunctionReflection $rFunction) {
        $this->assertEquals($expectedFnName, $rFunction->name());
        $this->assertEquals($expectedFilePath, $rFunction->filePath());
    }

    private function checkClasses(string $method, int $expectedCount, \Closure $check) {
        $filePath = $this->getTestDirPath() . '/Classes.php';
        $rFile = new FileReflection($filePath);
        $i = 0;
        foreach ($rFile->$method() as $rClass) {
            /** @var $rClass ClassTypeReflection */
            $this->assertInstanceOf(ClassTypeReflection::class, $rClass);
            $check($i, $rClass);
            $this->assertSame(__CLASS__, $rClass->nameOfNamespace());
            $i++;
        }
        $this->assertSame($expectedCount, $i);
    }
}
