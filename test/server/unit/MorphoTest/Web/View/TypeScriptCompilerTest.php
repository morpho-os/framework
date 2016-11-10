<?php
namespace MorphoTest\Web\View;

use Morpho\Test\TestCase;
use Morpho\Web\View\TypeScriptCompiler;

class TypeScriptCompilerTest extends TestCase {
    public function setUp() {
        parent::setUp();
        $this->compiler = new TypeScriptCompiler();
    }

    public function testWriteTsconfig() {
        $dirPath = $this->tmpDirPath();
        $filePath = $this->compiler->writeTsconfig($dirPath);
        $this->assertEquals($dirPath . "/tsconfig.json", $filePath);
        $config = json_decode(file_get_contents($filePath), true);
        $this->assertArrayHasKey('removeComments', $config);
    }

    public function testVersion() {
        $this->assertRegExp('~^Version\s+\d+\.\d+\.\d+~si', $this->compiler->version());
    }

    public function testOptionsAccessors() {
        $options = $this->compiler->getOptions();
        $this->assertTrue(count($options) > 0);
        $this->assertEquals('LF', $options['newLine']);
        $this->assertEquals(TypeScriptCompiler::MODULE_KIND, $this->compiler->getOption('module'));
    }

    public function testCompileToFile() {
        $inFilePath = $this->createTmpFile('ts');
        file_put_contents($inFilePath, 'export function main() {}');
        $outFilePath = dirname($inFilePath) . '/' . basename($inFilePath) . '-tsc/bar/test.js';
        $this->compiler->setOption('module', 'system');
        $res = $this->compiler->compileToFile($inFilePath, $outFilePath);
        $this->assertFalse($res->notSuccess());
        $this->assertRegExp('~^System\.register\(.*\}\);$~si', trim(file_get_contents($outFilePath)));
    }

    public function testCompileToDir() {
        $inFilePath = $this->createTmpFile('ts');
        file_put_contents($inFilePath, 'export function main() {}');
        $outDirPath = $this->createTmpDir();
        $this->compiler->setOption('module', 'umd');
        $res = $this->compiler->compileToDir($inFilePath, $outDirPath);
        $this->assertFalse($res->notSuccess());
        $outFilePath = $outDirPath . '/' . basename($inFilePath, '.ts') . '.js';
        $this->assertRegExp('~\(function \((dependencies, )?factory\) \{.*\}\);$~si', trim(file_get_contents($outFilePath)));
    }
}
