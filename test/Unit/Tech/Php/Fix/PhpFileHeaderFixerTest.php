<?php declare(strict_types=1);


namespace Morpho\Test\Unit\Tech\Fix;


use Morpho\Base\Err;
use Morpho\Base\Ok;
use Morpho\Tech\Php\Fix\PhpFileHeaderFixer;
use Morpho\Testing\TestCase;

class PhpFileHeaderFixerTest extends TestCase {
    public function testCheckAndFix() {
        $fixer = new PhpFileHeaderFixer();
        $filePath = $this->getTestDirPath() . '/Foo.php';
        $context = [
            'filePath' => $filePath,
            'baseDirPath' => dirname($filePath),
            'ns' => self::class,
        ];

        $checkResult = $fixer->check($context);

        $this->assertInstanceOf(Err::class, $checkResult);
        $resultContext = $checkResult->val();
        $this->assertTrue($resultContext['hasDeclare']);
        $this->assertTrue($resultContext['hasValidDeclare']);
        $this->assertFalse($resultContext['hasLicenseComment']);
        $this->assertEquals(new Ok(['expected' => $resultContext['ns'], 'actual' => $resultContext['ns']]), $resultContext['nsCheckResult']);
        $this->assertEquals(new Ok(['expected' => 'Foo', 'actual' => 'Foo']), $resultContext['classTypeCheckResult']);

        $fixResult = $fixer->fix($resultContext);

        $this->assertInstanceOf(Ok::class, $fixResult);
        $resultContext = $fixResult->val();
        $this->assertMatchesRegularExpression('~^\\<\\?php\\s+declare\\s*\\(strict_types\\=1\\);\s+'
            . preg_quote(<<<'OUT'
            /**
             * This file is part of morpho-os/framework
             * It is distributed under the 'Apache License Version 2.0' license.
             * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
             */
            OUT) . '\s+namespace Morpho\\\\Test\\\\Unit\\\\Tech\\\\Fix\\\\PhpFileHeaderFixerTest;
~s', $resultContext['fixed']);
    }

    public function testAddDeclare() {
        $this->markTestIncomplete();
    }
}