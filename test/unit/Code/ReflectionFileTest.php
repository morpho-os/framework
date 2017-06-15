<?php declare(strict_types=1);
namespace MorphoTest\Code;

use Morpho\Code\ReflectionFile;
use Morpho\Code\ReflectionNamespace;
use Morpho\Test\TestCase;
use Morpho\Code\ReflectionClass;
use ReflectionFunction;

class ReflectionFileTest extends TestCase {
    public function testFilePath() {
        $filePath = $this->getTestDirPath() . '/empty-file.php';
        $rFile = new ReflectionFile($filePath);
        $this->assertEquals($filePath, $rFile->filePath());
    }

    public function testNamespaces_EmptyFile() {
        $filePath = $this->getTestDirPath() . '/empty-file.php';
        $rFile = new ReflectionFile($filePath);
        $this->assertGenYields([], $rFile->namespaces());
    }

    public function testNamespaces_GlobalNamespace() {
        $filePath = $this->getTestDirPath() . '/global-ns.php';
        $rFile = new ReflectionFile($filePath);
        $i = 0;
        foreach ($rFile->namespaces() as $rNamespace) {
            $this->assertInstanceOf(ReflectionNamespace::class, $rNamespace);
            $this->assertNull($rNamespace->name());
            $this->assertTrue($rNamespace->isGlobal());
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
        $rFile = new ReflectionFile($filePath);
        $i = 0;
        foreach ($rFile->namespaces() as $rNamespace) {
            $this->assertInstanceOf(ReflectionNamespace::class, $rNamespace);
            $this->assertFalse($rNamespace->isGlobal());
            $this->assertEquals($filePath, $rNamespace->filePath());

            $this->checkClassTypes($expected[$i]['classes'], $filePath, $rNamespace);
            $this->checkFunctions($expected[$i]['functions'], $filePath, $rNamespace);

            $i++;
        }
        $this->assertEquals(2, $i);
    }

    private function assertGenYields($expected, \Generator $gen) {
        $this->assertEquals($expected, iterator_to_array($gen, false));
    }

    private function checkClassTypes(array $expectedClasses, string $filePath, ReflectionNamespace $rNamespace) {
        $j = 0;
        foreach ($rNamespace->classTypes() as $rClass) {
            $this->checkReflectionClass($expectedClasses[$j], $filePath, $rClass);
            $j++;
        }
        $this->assertEquals(count($expectedClasses), $j);
    }

    private function checkFunctions(array $expectedFns, string $filePath, ReflectionNamespace $rNamespace) {
        $j = 0;
        foreach ($rNamespace->functions() as $rFunction) {
            $this->checkReflectionFunction($expectedFns[$j], $filePath, $rFunction);
            $j++;
        }
        $this->assertEquals(count($expectedFns), $j);
    }

    private function checkReflectionClass(string $expectedClass, string $expectedFilePath, ReflectionClass $rClass) {
        $this->assertEquals($expectedClass, $rClass->getName());
        $this->assertEquals($expectedFilePath, $rClass->getFileName());
    }

    private function checkReflectionFunction(string $expectedFnName, string $expectedFilePath, ReflectionFunction $rFunction) {
        $this->assertEquals($expectedFnName, $rFunction->getName());
        $this->assertEquals($expectedFilePath, $rFunction->getFileName());
    }
}