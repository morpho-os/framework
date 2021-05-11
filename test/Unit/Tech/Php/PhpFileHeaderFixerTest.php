<?php declare(strict_types=1);


namespace Morpho\Test\Unit\Tech;


use Morpho\Base\Err;
use Morpho\Base\Ok;
use Morpho\Tech\Php\PhpFileHeaderFixer;
use Morpho\Testing\TestCase;

class PhpFileHeaderFixerTest extends TestCase {
    private PhpFileHeaderFixer $fixer;

    public function setUp(): void {
        parent::setUp();
        $this->fixer = new PhpFileHeaderFixer();
    }

    public function dataCheckAndFix_EmptyFile() {
        //yield [''];
        //yield ['<?php'];
        yield ["#!/usr/bin/env php"];
        yield ["#!/usr/bin/env php\n<?php"];
        yield ["#!/usr/bin/env php\n<?php\n"];
    }

    /**
     * @dataProvider dataCheckAndFix_EmptyFile
     * @param $text
     */
    public function testCheckAndFix_EmptyFile($text) {
        $tmpFilePath = $this->createTmpFile();
        file_put_contents($tmpFilePath, $text);
        $context = [
            'baseDirPath' => dirname($tmpFilePath),
            'filePath'    => $tmpFilePath,
            'ns'          => 'Some',
        ];

        $checkResult = $this->fixer->check($context);

        $resultContext = $checkResult->val();
        $this->assertInstanceOf(Err::class, $checkResult);
        $this->checkContext(
            array_merge(
                $context,
                [
                    'hasStmts'             => false,
                    'hasDeclare'           => false,
                    'hasValidDeclare'      => false,
                    'hasLicenseComment'    => false,
                    'nsCheckResult'        => new Err(['expected' => 'Some', 'actual' => null]),
                    'classTypeCheckResult' => new Ok((['expected' => null, 'actual' => null])),
                ],
            ),
            $resultContext
        );

        $fixResult = $this->fixer->fix($resultContext);

        $this->assertEquals(new Err("The file '{$context['filePath']}' does not have PHP statements"), $fixResult);
    }

    public function testCheckAndFix_DeclareAndNamespace_NoLicense() {
        $filePath = $this->getTestDirPath() . '/Foo.php';
        $context = [
            'filePath'    => $filePath,
            'baseDirPath' => dirname($filePath),
            'ns'          => self::class,
        ];

        $checkResult = $this->fixer->check($context);

        $this->assertInstanceOf(Err::class, $checkResult);
        $resultContext = $checkResult->val();

        $this->checkContext(
            array_merge(
                $context,
                [
                    'hasStmts'             => true,
                    'hasDeclare'           => true,
                    'hasValidDeclare'      => true,
                    'hasLicenseComment'    => false,
                    'nsCheckResult'        => new Ok(['expected' => $context['ns'], 'actual' => $context['ns']]),
                    'classTypeCheckResult' => new Ok(['expected' => 'Foo', 'actual' => 'Foo']),
                ]
            ),
            $resultContext
        );

        $fixResult = $this->fixer->fix($resultContext);

        $this->assertInstanceOf(Ok::class, $fixResult);
        $resultContext = $fixResult->val();
        $this->assertMatchesRegularExpression($this->fileHeaderRe(__CLASS__), $resultContext['text']);
    }

    public function testCheckAndFix_DeclareAndLicense_NoNs() {
        $filePath = $this->getTestDirPath() . '/bar.php';
        $context = [
            'filePath'    => $filePath,
            'baseDirPath' => dirname($filePath),
            'ns'          => self::class,
        ];

        $checkResult = $this->fixer->check($context);

        $this->assertInstanceOf(Err::class, $checkResult);
        $resultContext = $checkResult->val();
        $this->checkContext(
            array_merge(
                $context,
                [
                    'hasStmts'             => true,
                    'hasDeclare'           => true,
                    'hasValidDeclare'      => true,
                    'hasLicenseComment'    => true,
                    'nsCheckResult'        => new Err(['expected' => $context['ns'], 'actual' => null]),
                    'classTypeCheckResult' => new Ok(['expected' => null, 'actual' => null]),
                ]
            ),
            $resultContext
        );

        $fixResult = $this->fixer->fix($resultContext);

        $this->assertInstanceOf(Ok::class, $fixResult);
        $resultContext = $fixResult->val();
        $this->assertMatchesRegularExpression($this->fileHeaderRe(__CLASS__), $resultContext['text']);
    }

    public function testCheck_MultipleDocComments() {
        $filePath = $this->getTestDirPath() . '/multiple-doc-comments.php';
        $context = [
            'filePath'    => $filePath,
            'baseDirPath' => dirname($filePath),
            'ns'          => self::class,
        ];

        $checkResult = $this->fixer->check($context);

        $this->assertInstanceOf(Ok::class, $checkResult);
        $resultContext = $checkResult->val();
        $this->checkContext(
            array_merge(
                $context,
                [
                    'hasStmts'             => true,
                    'hasDeclare'           => true,
                    'hasValidDeclare'      => true,
                    'hasLicenseComment'    => true,
                    'nsCheckResult'        => new Ok(['expected' => $context['ns'], 'actual' => $context['ns']]),
                    'classTypeCheckResult' => new Ok(['expected' => null, 'actual' => null]),
                ]
            ),
            $resultContext
        );
    }

    public function testCheckAndFix_LicenseInInvalidPlace() {
        $filePath = $this->getTestDirPath() . '/LicenseCommentInInvalidPlace.php';
        $context = [
            'filePath'    => $filePath,
            'baseDirPath' => dirname($filePath),
            'ns'          => self::class,
        ];

        $checkResult = $this->fixer->check($context);

        $this->assertInstanceOf(Ok::class, $checkResult);
        $resultContext = $checkResult->val();
        $this->checkContext(
            array_merge(
                $context,
                [
                    'hasStmts'             => true,
                    'hasDeclare'           => true,
                    'hasValidDeclare'      => true,
                    'hasLicenseComment'    => true,
                    'nsCheckResult'        => new Ok(['expected' => $context['ns'], 'actual' => $context['ns']]),
                    'classTypeCheckResult' => new Ok(['expected' => 'LicenseCommentInInvalidPlace', 'actual' => 'LicenseCommentInInvalidPlace']),
                ]
            ),
            $resultContext
        );

        $fixResult = $this->fixer->fix($resultContext);

        $this->assertInstanceOf(Ok::class, $fixResult);
        $resultContext = $fixResult->val();

        $this->assertMatchesRegularExpression($this->fileHeaderRe(self::class), $resultContext['text']);
        $this->assertSame(1, substr_count($resultContext['text'], 'This file is part of morpho-os/framework'));
    }

    public function testCheckAndFix_OtherDocCommentAfterDeclare() {
        $filePath = $this->getTestDirPath() . '/Rand.php';
        $context = [
            'filePath'    => $filePath,
            'baseDirPath' => dirname($filePath),
            'ns'          => self::class,
        ];

        $checkResult = $this->fixer->check($context);

        $this->assertInstanceOf(Err::class, $checkResult);
        $resultContext = $checkResult->val();
        $this->checkContext(
            array_merge(
                $context,
                [
                    'hasStmts'             => true,
                    'hasDeclare'           => true,
                    'hasValidDeclare'      => true,
                    'hasLicenseComment'    => false,
                    'nsCheckResult'        => new Ok(['expected' => $context['ns'], 'actual' => $context['ns']]),
                    'classTypeCheckResult' => new Ok(['expected' => 'Rand', 'actual' => 'Rand']),
                ]
            ),
            $resultContext
        );

        $fixResult = $this->fixer->fix($resultContext);

        $this->assertInstanceOf(Ok::class, $fixResult);
        $resultContext = $fixResult->val();
        $this->assertMatchesRegularExpression(
            $this->fileHeaderRe(
                __CLASS__, $this->licenseComment() . "\n" . <<<'OUT'
                /**
                 * Pseudorandom number generator (PRNG)
                 */
                OUT
            ),
            $resultContext['text']
        );
    }

    private function fileHeaderRe(string $expectedNs, string $licenseComment = null): string {
        return '~^\\<\\?php\\s+declare\\s*\\(strict_types\\=1\\);\s+'
            . preg_quote($licenseComment ?? $this->licenseComment(), '~')
            . '\s+namespace ' . preg_quote($expectedNs, '~') . '~s';
    }

    private function checkContext(array $expectedContext, array $actualContext): void {
        $this->assertSame($expectedContext['filePath'], $actualContext['filePath']);
        $this->assertSame($expectedContext['ns'], $actualContext['ns']);
        $this->assertSame($expectedContext['baseDirPath'], $actualContext['baseDirPath']);
        $this->assertSame($expectedContext['hasStmts'], $actualContext['hasStmts']);
        $this->assertSame($expectedContext['hasDeclare'], $actualContext['hasDeclare']);
        $this->assertSame($expectedContext['hasValidDeclare'], $actualContext['hasValidDeclare']);
        $this->assertSame($expectedContext['hasLicenseComment'], $actualContext['hasLicenseComment']);
        $this->assertEquals($expectedContext['nsCheckResult'], $actualContext['nsCheckResult']);
        $this->assertEquals($expectedContext['classTypeCheckResult'], $actualContext['classTypeCheckResult']);
    }

    private function licenseComment(): string {
        return <<<'OUT'
        /**
         * This file is part of morpho-os/framework
         * It is distributed under the 'Apache License Version 2.0' license.
         * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
         */
        OUT;
    }
}