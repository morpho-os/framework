<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Infra;

use Morpho\Infra\LicenseHeaderManager;
use Morpho\Test\TestCase;

class LicenseHeaderManagerTest extends TestCase {
    public function testUpdateLicenseHeader_ThrowsExceptionForInvalidLicenseText() {
        $filePath = $this->createTmpFile();
        $this->expectException(\InvalidArgumentException::class, "The license text must contain the both words: 'license' and 'morpho'");
        (new LicenseHeaderManager())->updateLicenseHeader($filePath, 'foo, bar');
    }

    public function testUpdateLicenseHeader_AddHeader_EmptyFile() {
        $filePath = $this->createTmpFile();
        $licenseText = $expectedText = <<<OUT
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
OUT;
        $this->checkLicense($filePath, $licenseText, $expectedText);
    }

    public function testUpdateLicenseHeader_AddHeader_NonEmptyFile() {
        $filePath = $this->createTmpFile();
        $licenseText = <<<OUT
/**
 * This file is part of
 * morpho-os/framework
 * see license
 */
OUT;
        file_put_contents($filePath, <<<OUT
foo
bar
baz
OUT
        );
        $expectedText = <<<OUT
/**
 * This file is part of
 * morpho-os/framework
 * see license
 */
foo
bar
baz
OUT;
        $this->checkLicense($filePath, $licenseText, $expectedText);
    }
    
    public function testUpdateLicenseHeader_AddHeader_FileWithShebang() {
        $filePath = $this->createTmpFile();
        $licenseText = <<<OUT
/**
 * This file is part of
 * morpho-os/framework
 * see license
 */
OUT;
        file_put_contents($filePath, <<<OUT
#!/usr/bin/php
<?php
echo "Hello World";
OUT
        );
        $expectedText = <<<OUT
#!/usr/bin/php
<?php
/**
 * This file is part of
 * morpho-os/framework
 * see license
 */
echo "Hello World";
OUT;
        $this->checkLicense($filePath, $licenseText, $expectedText);
    }

    public function testUpdateLicenseHeader_AddHeader_WithoutShebang() {
        $filePath = $this->createTmpFile();
        $licenseText = <<<OUT
/**
 * This file is part of
 * morpho-os/framework
 * see license
 */
OUT;
        file_put_contents($filePath, <<<OUT
<?php
echo "Hello World";
OUT
        );
        $expectedText = <<<OUT
<?php
/**
 * This file is part of
 * morpho-os/framework
 * see license
 */
echo "Hello World";
OUT;
        $this->checkLicense($filePath, $licenseText, $expectedText);
    }

    public function testUpdateLicenseHeader_AddHeader_LicenseWithComments() {
        $filePath = $this->createTmpFile();
        $licenseText = <<<OUT
/**
 * This file is part of
 * morpho-os/framework
 * see license
 */
OUT;
        file_put_contents($filePath, <<<OUT
<?php
echo "Hello World";
OUT
        );
        $expectedText = <<<OUT
<?php
/**
 * This file is part of
 * morpho-os/framework
 * see license
 */
echo "Hello World";
OUT;
        $this->checkLicense($filePath, $licenseText, $expectedText);
    }

    public function dataForUpdateLicenseHeader_DifferentLicenseHeader() {
        return [
            [
                "/**\n * This is a new morpho license\n */",
            ],
            [
                'This is a new morpho license',
            ],
        ];
    }

    /**
     * @dataProvider dataForUpdateLicenseHeader_DifferentLicenseHeader
     */
    public function testUpdateLicenseHeader_DifferentLicenseHeader($newLicenseText) {
        $filePath = $this->createTmpFile();
        file_put_contents($filePath, <<<OUT
<?php
/**
 * This file is part of
 * morpho-os/framework
 * see license
 */
echo "Hello World";
OUT
        );

        (new LicenseHeaderManager())->updateLicenseHeader($filePath, $newLicenseText);

        $newFileText = file_get_contents($filePath);
        $this->assertSame(<<<OUT
<?php
/**
 * This is a new morpho license
 */
echo "Hello World";
OUT
            , $newFileText
        );
    }

    public function testRemoveLicenseHeader() {
        $filePath = $this->createTmpFile();
        file_put_contents($filePath, <<<OUT
<?php
/**
 * This file is part of
 * morpho-os/framework
 * see license
 */
echo "Hello World";
OUT
        );
        (new LicenseHeaderManager())->removeLicenseHeader($filePath);
        $this->assertSame(<<<OUT
<?php
echo "Hello World";
OUT
            , file_get_contents($filePath)
        );
    }

    public function dataForFindLicenseHeaderInFile() {
        $testDirPath = $this->getTestDirPath();
        $licenseText = <<<OUT
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text
 */
OUT;
        return [
            [
                [$licenseText, 31], $testDirPath . '/file-with-header.php',
            ],
            [
                [$licenseText, 6], $testDirPath . '/file-with-header1.php',
            ],
            [
                [$licenseText, 50], $testDirPath . '/file-with-header2.php',
            ],
            [
                [$licenseText, 25], $testDirPath . '/file-with-header3.php',
            ],
            [
                false, $testDirPath . '/file-without-header.php',
            ],
        ];
    }

    /**
     * @dataProvider dataForFindLicenseHeaderInFile
     */
    public function testFindLicenseHeaderInFile($expected, $filePath) {
        $this->assertSame($expected, (new LicenseHeaderManager())->findLicenseHeaderInFile($filePath));
    }
    
    private function checkLicense($filePath, $licenseText, $expectedText) {
        $licenseHeaderManager = new LicenseHeaderManager();

        $licenseHeaderManager->updateLicenseHeader($filePath, $licenseText);

        $checkFileText = function () use ($filePath, $expectedText) {
            $actualText = file_get_contents($filePath);
            $this->assertSame($expectedText, $actualText);
        };
        $checkFileText();

        // Should not add license at second call.
        $licenseHeaderManager->updateLicenseHeader($filePath, $licenseText);
        $checkFileText();
    }
}