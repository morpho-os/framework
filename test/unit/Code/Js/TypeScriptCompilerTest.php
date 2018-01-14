<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Code\Js;

use Morpho\Base\IFn;
use Morpho\Test\TestCase;
use Morpho\Code\Js\TypeScriptCompiler;

class TypeScriptCompilerTest extends TestCase {
    /**
     * @var TypeScriptCompiler
     */
    private $compiler;

    public function setUp() {
        parent::setUp();
        $this->compiler = new TypeScriptCompiler();
    }

    public function testInheritance() {
        $this->assertInstanceOf(IFn::class, $this->compiler);
    }

    public function testInvoke_SingleInFileToSingleOutFile() {
        $inFilePath = $this->createTmpFile('ts');
        file_put_contents($inFilePath, 'export function main() {}');
        $outFilePath = dirname($inFilePath) . '/' . basename($inFilePath) . '-tsc/bar/test.js';
        $compilerConfig = ['module' => 'system', 'outFile' => $outFilePath, $inFilePath];

        $this->compiler->compilerConfig()->merge($compilerConfig);
        $res = $this->compiler->__invoke([]);

        $this->assertFalse($res->isError());
        $this->assertRegExp('~^System\.register\(.*\}\);\n//# sourceMappingURL=test.js.map$~si', trim(file_get_contents($outFilePath)));
    }

    public function testWriteTsconfigFile_Default() {
        $dirPath = $this->tmpDirPath();

        $filePath = $this->compiler->writeTsconfigFile($dirPath);

        $this->assertEquals($dirPath . "/tsconfig.json", $filePath);
        $config = json_decode(file_get_contents($filePath), true);
        $this->assertTrue($config['compilerOptions']['removeComments']);
    }

    public function testWriteTsConfigFile_OverwriteCompilerOption() {
        $tmpDirPath = $this->createTmpDir();

        $tsConfigFilePath = $this->compiler->writeTsconfigFile($tmpDirPath, ['compilerOptions' => ['removeComments' => true]]);

        $json = json_decode(file_get_contents($tsConfigFilePath), true);
        $this->assertTrue($json['compilerOptions']['removeComments']);
    }

    public function testOptionsString() {
        $option = 'strictNullChecks';
        $this->assertNotContains('--' . $option, $this->compiler->compilerConfigStr([$option => false]));
        $this->assertContains('--' . $option, $this->compiler->compilerConfigStr([$option => true]));
    }

    public function testVersion() {
        $this->assertRegExp('~^\d+\.\d+\.\d+~si', $this->compiler->version());
    }

    public function testCompilerConfigAccessors() {
        $compilerConfig = $this->compiler->compilerConfig();
        $this->assertTrue(count($compilerConfig) > 0);
        $this->assertEquals('lf', $compilerConfig['newLine']);
        $this->assertEquals($compilerConfig::MODULE_KIND, $compilerConfig['module']);
    }

    public function testCompileToFile_SingleInFileToSingleOutFile() {
        $inFilePath = $this->createTmpFile('ts');
        file_put_contents($inFilePath, 'export function main() {}');
        $outFilePath = dirname($inFilePath) . '/' . basename($inFilePath) . '-tsc/bar/test.js';
        $this->compiler->compilerConfig()['module'] = 'system';

        $res = $this->compiler->compileToFile($inFilePath, $outFilePath);

        $this->assertFalse($res->isError());
        $this->assertRegExp('~^System\.register\(.*\}\);\n//# sourceMappingURL=test.js.map$~si', trim(file_get_contents($outFilePath)));
    }

    public function testCompileToFile_MultipleInFilesToSingleOutFile() {
        $tmpDirPath = $this->createTmpDir();
        $inFilePath1 = $tmpDirPath . '/foo.ts';
        $inFilePath2 = $tmpDirPath . '/bar.ts';
        file_put_contents($inFilePath1, <<<OUT
export function foo() {}
OUT
        );
        file_put_contents($inFilePath2, <<<OUT
export function bar() {}
OUT
        );
        $outFilePath = $tmpDirPath . '/combined.js';

        $this->compiler->compileToFile([$inFilePath1, $inFilePath2], $outFilePath);

        $ts = file_get_contents($outFilePath);
        $this->assertContains('function foo()', $ts);
        $this->assertContains('function bar()', $ts);
    }

    public function testCompileFile_MultipleInFilesToMultipleOutFiles() {
        $tmpDirPath = $this->createTmpDir();
        $inFilePath1 = $tmpDirPath . '/foo.ts';
        $inFilePath2 = $tmpDirPath . '/bar.ts';
        file_put_contents($inFilePath1, <<<OUT
export function foo() {}
OUT
        );
        file_put_contents($inFilePath2, <<<OUT
export function bar() {}
OUT
        );

        $this->compiler->compileToFile([$inFilePath1, $inFilePath2]);

        $this->assertContains('function foo()', file_get_contents($tmpDirPath . '/foo.js'));
        $this->assertContains('function bar()', file_get_contents($tmpDirPath . '/bar.js'));
    }

    public function testCompileToDir() {
        $inFilePath = $this->createTmpFile('ts');
        file_put_contents($inFilePath, 'export function main() {}');
        $outDirPath = $this->createTmpDir();
        $this->compiler->compilerConfig()['module'] = 'umd';

        $res = $this->compiler->compileToDir($inFilePath, $outDirPath);

        $this->assertFalse($res->isError());
        $outFilePath = $outDirPath . '/' . basename($inFilePath, '.ts') . '.js';
        $this->assertRegExp('~^\(function \((dependencies, )?factory\) \{.*\}\);\n//# sourceMappingURL=.*?\.js\.map$~si', trim(file_get_contents($outFilePath)));
    }

    public function testCompilerConfigStr_HandlesArraysProperly() {
        $this->compiler->compilerConfig()['types'] = ['jquery', 'mocha'];
        $this->assertContains("'--types' 'jquery,mocha'", $this->compiler->compilerConfigStr());
    }
}
