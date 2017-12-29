<?php declare(strict_types=1);
namespace MorphoTest\Unit\Infra;

use Morpho\Infra\DeclareStmtManager;
use Morpho\Test\TestCase;

class DeclareStmtManagerTest extends TestCase {
    public function dataForRemoveCommentedOutDeclareStmt() {
        $expected = <<<OUT
<?php
/**
 * This file is part of morpho-os/framework
 */
echo "Test";
OUT;
        yield [
            <<<OUT
<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 */
echo "Test";
OUT
            , $expected
        ];
        yield [
            <<<OUT
<?php
//declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 */
echo "Test";
OUT
            , $expected
        ];
        yield [
            <<<OUT
<?php
/**
 * This file is part of morpho-os/framework
 */
//declare(strict_types=1);
echo "Test";
OUT
            , $expected
        ];

        yield [<<<OUT
<?php
//declare(strict_types=1);
OUT
            , '<?php'
        ];
        yield [
            <<<OUT
<?php
/**
 * This file is part of morpho-os/framework
 */
echo "Test";
OUT
            ,
            <<<OUT
<?php
/**
 * This file is part of morpho-os/framework
 */
echo "Test";
OUT
        ];
        yield [
            <<<OUT
<?php
OUT
            , '<?php'
        ];
        yield [
            '',
            '',
        ];
    }

    /**
     * @dataProvider dataForRemoveCommentedOutDeclareStmt
     */
    public function testRemoveCommentedOutDeclareStmt(string $code, string $expected) {
        $manager = new DeclareStmtManager();
        $this->assertSame($expected, $manager->removeCommentedOutDeclareStmt($code));
    }
}