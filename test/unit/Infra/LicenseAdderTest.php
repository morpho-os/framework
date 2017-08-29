<?php declare(strict_types=1);
namespace Morpho\Infra;

use Morpho\Test\TestCase;

class LicenseAdderTest extends TestCase {
    public function testAddLicenseHeader_EmptyFile() {
        $filePath = $this->createTmpFile();
        $licenseText = "This file is part of\nmorpho-os/framework";
        $expectedText = <<<OUT
/**
 * This file is part of
 * morpho-os/framework
 */
OUT;
        $this->checkLicense($filePath, $licenseText, $expectedText);
    }

    public function testAddLicenseHeader_NonEmptyFile() {
        $filePath = $this->createTmpFile();
        $licenseText = "This file is part of\nmorpho-os/framework";
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
 */
foo
bar
baz
OUT;
        $this->checkLicense($filePath, $licenseText, $expectedText);
    }
    
    public function testAddLicenseHeader_FileWithShebang() {
        $filePath = $this->createTmpFile();
        $licenseText = "This file is part of\nmorpho-os/framework";
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
 */
echo "Hello World";
OUT;
        $this->checkLicense($filePath, $licenseText, $expectedText);
    }

    public function testAddLicenseHeader_WithoutShebang() {
        $filePath = $this->createTmpFile();
        $licenseText = "This file is part of\nmorpho-os/framework";
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
 */
echo "Hello World";
OUT;
        $this->checkLicense($filePath, $licenseText, $expectedText);
    }
    
    private function checkLicense($filePath, $licenseText, $expectedText) {
        $licenseAdder = new LicenseAdder();

        $licenseAdder->addLicenseForFile($filePath, $licenseText);

        $checkFileText = function () use ($filePath, $expectedText) {
            $actualText = file_get_contents($filePath);
            $this->assertSame($expectedText, $actualText);
        };
        $checkFileText();

        // Should not add license at second call.
        $licenseAdder->addLicenseForFile($filePath, $licenseText);
        $checkFileText();
    }
}