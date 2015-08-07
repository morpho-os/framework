<?php
namespace MorphoTest\Code\CodeGen;

use Morpho\Test\TestCase;
use Morpho\Code\CodeGen\BuilderFactory;
use Morpho\Code\CodeGen\PrettyPrinter;

class CodeGenTest extends TestCase {
    /*
    public function testGenerateFileWithOneNamespace()
    {
        $builderFactory = new BuilderFactory();
        $nsBuilder = $builderFactory->namespace(null);
        $nsBuilder->add
    }
    */
    public function testCreateFileBuilder() {
        $this->assertInstanceOf('\PhpParser\BuilderAbstract', (new BuilderFactory())->file());
    }

    public function testGenerateFileWithOneNamespace() {
        $builderFactory = new BuilderFactory();
        $fileBuilder = $builderFactory->file();

        $this->addFirstNsStmts($builderFactory, $fileBuilder);

        $expected = <<<OUT
<?php
namespace My\FirstNs;

use Zend\Stdlib\ArrayObject as FooAlias;
use Symfony\Component\Yaml\Escaper as BarAlias;

class FirstClass
{
}

class SecondClass
{
}
OUT;
        $this->assertEquals($expected, $this->prettyPrint($fileBuilder));
    }

    public function testGenerateFileWithMultipleNamespaces() {
        $builderFactory = new BuilderFactory();
        $fileBuilder = $builderFactory->file();

        $fileBuilder
            ->addGlobalNs()
            ->addUse('SomeNs\SomeTrait')
            ->addStmt($builderFactory->class('Test'))
            ->addNs('My\FirstNs')
            ->addUse('Zend\Validator\Hostname')
            ->addUse('Zend\Http\Request', 'HttpRequest')
            ->addStmts([$builderFactory->class('FirstClass'), $builderFactory->class('SecondClass')])
            ->addGlobalNs()
            ->addUse('SomeOtherNs\SomeOtherClass')
            ->addUse('PHPUnit_Framework_TestCase', 'BaseTest')
            ->addStmts([$builderFactory->class('MyTest')->extend('BaseTest'), $builderFactory->class('MyOther')]);

        $expected = <<<OUT
<?php
namespace {
    use SomeNs\SomeTrait;
    use SomeOtherNs\SomeOtherClass;
    use PHPUnit_Framework_TestCase as BaseTest;

    class Test
    {
    }

    class MyTest extends BaseTest
    {
    }

    class MyOther
    {
    }
}

namespace My\FirstNs {
    use Zend\Validator\Hostname;
    use Zend\Http\Request as HttpRequest;

    class FirstClass
    {
    }

    class SecondClass
    {
    }
}
OUT;
        $this->assertEquals($expected, $this->prettyPrint($fileBuilder));
    }

    public function testOnlyGlobalNs() {
        $builderFactory = new BuilderFactory();
        $fileBuilder = $builderFactory->file();

        $fileBuilder->addUse('Foo\Bar')
            ->addStmt($builderFactory->class('Test')->extend('ArrayObject'));

        $expected = <<<OUT
<?php
use Foo\Bar;

class Test extends ArrayObject
{
}
OUT;
        $this->assertEquals($expected, $this->prettyPrint($fileBuilder));
    }

    private function addFirstNsStmts($builderFactory, $fileBuilder) {
        $fileBuilder->addNs('My\FirstNs')
            ->addUse('Zend\Stdlib\ArrayObject', 'FooAlias')
            ->addUse('Symfony\Component\Yaml\Escaper', 'BarAlias')
            ->addStmt($builderFactory->class('FirstClass'))
            ->addStmt($builderFactory->class('SecondClass')->getNode());
    }

    private function prettyPrint($fileBuilder) {
        return (new PrettyPrinter)->prettyPrintFile([$fileBuilder->getNode()]);
    }
}
