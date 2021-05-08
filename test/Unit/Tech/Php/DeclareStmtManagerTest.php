<?php declare(strict_types=1);
namespace Morpho\Test\Unit\Tech\Php;

use Morpho\Tech\Php\DeclareStmtManager;
use Morpho\Testing\TestCase;

class DeclareStmtManagerTest extends TestCase {
    public function dataRemoveCommentedOutDeclareStmt() {
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
            ,
            $expected,
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
            ,
            $expected,
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
            ,
            $expected,
        ];

        yield [
            <<<OUT
<?php
//declare(strict_types=1);
OUT
            ,
            '<?php',
        ];
        yield from $this->commonCases();
    }

    /**
     * @dataProvider dataRemoveCommentedOutDeclareStmt
     */
    public function testRemoveCommentedOutDeclareStmt(string $code, string $expected) {
        $manager = new DeclareStmtManager();
        $this->assertSame($expected, $manager->removeCommentedOutDeclareStmt($code));
    }

    public function dataRemoveDeclareStmt() {
        $expected = <<<OUT
<?php
/**
 * This file is part of morpho-os/framework
 */
echo "Test";
OUT;
        yield [
            <<<OUT
<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 */
echo "Test";
OUT
            ,
            $expected,
        ];
        yield [
            <<<OUT
<?php
//declare(strict_types=1);
OUT
            ,
            <<<OUT
<?php
//declare(strict_types=1);
OUT
    ,
        ];
        yield [
            <<<OUT
<?php
//declare(strict_types=1);
declare(strict_types=1);
OUT
            ,
            <<<OUT
<?php
//declare(strict_types=1);
OUT
    ,
        ];
        yield from $this->commonCases();
    }

    /**
     * @dataProvider dataRemoveDeclareStmt
     */
    public function testRemoveDeclareStmt(string $code, string $expected) {
        $manager = new DeclareStmtManager();
        $this->assertSame($expected, $manager->removeDeclareStmt($code));
    }

    public function dataAddDeclareStmt() {
        $sample = <<<OUT
<?php
/**
 * Multi-line
 * comment
 */
namespace Foo\Bar;
OUT;

        yield [
            $sample,
            DeclareStmtManager::AT_FIRST_LINE,
            <<<OUT
<?php declare(strict_types=1);
/**
 * Multi-line
 * comment
 */
namespace Foo\Bar;
OUT
    ,
        ];

        yield [
            $sample,
            DeclareStmtManager::AT_SECOND_LINE,
            <<<OUT
<?php
declare(strict_types=1);
/**
 * Multi-line
 * comment
 */
namespace Foo\Bar;
OUT
    ,
        ];

        yield [
            $sample,
            DeclareStmtManager::AFTER_FIRST_MULTI_COMMENT,
            <<<OUT
<?php
/**
 * Multi-line
 * comment
 */
declare(strict_types=1);
namespace Foo\Bar;
OUT
    ,
        ];

        $sample = <<<OUT
<?php
namespace Foo\Bar;
OUT;

        yield [
            $sample,
            DeclareStmtManager::AT_FIRST_LINE,
            <<<OUT
<?php declare(strict_types=1);
namespace Foo\Bar;
OUT
    ,
        ];

        yield [
            $sample,
            DeclareStmtManager::AT_SECOND_LINE,
            <<<OUT
<?php
declare(strict_types=1);
namespace Foo\Bar;
OUT
    ,
        ];

        yield [
            $sample,
            DeclareStmtManager::AFTER_FIRST_MULTI_COMMENT,
            <<<OUT
<?php
namespace Foo\Bar;
OUT
    ,
        ];

        yield [
            '',
            DeclareStmtManager::AT_FIRST_LINE,
            '',
        ];

        yield [
            '<?php',
            DeclareStmtManager::AT_FIRST_LINE,
            <<<OUT
<?php declare(strict_types=1);
OUT
    ,
        ];
    }

    /**
     * @dataProvider dataAddDeclareStmt
     */
    public function testAddDeclareStmt(string $code, int $position, string $expected) {
        $manager = new DeclareStmtManager();
        $this->assertSame($expected, $manager->addDeclareStmt($code, $position));
    }

    private function commonCases(): iterable {
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
    ,
        ];
        yield [
            "<?php\n",
            "<?php\n",
        ];
        yield [
            '<?php',
            '<?php',
        ];
        yield [
            '',
            '',
        ];
    }
}