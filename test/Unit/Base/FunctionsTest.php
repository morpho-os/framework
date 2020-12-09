<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Base;

use ArrayIterator;
use Closure;
use Generator;
use IteratorAggregate;
use Morpho\Base\IDisposable;
use Morpho\Base\IFn;
use Morpho\Testing\TestCase;
use SplStack;
use stdClass;
use UnexpectedValueException;
use function call_user_func;
use function count;
use function fclose;
use function file_put_contents;
use function Morpho\Base\{formatFloat, it, last, lastPos, lines, memoize, not, op, qq, setProps, fromJson, partial, compose, toJson, tpl, uniqueName, deleteDups, classify, trimMore, sanitize, underscore, dasherize, camelize, humanize, titleize, shorten, showLn, normalizeEols, using, waitUntilNoOfAttempts, waitUntilTimeout, q, formatBytes, words, ucfirst, indent, unindent};
use RuntimeException;
use function get_class_methods;
use function ob_get_clean;
use function ob_start;
use function property_exists;

class FunctionsTest extends TestCase {
    private $tmpHandle;

    public function tearDown(): void {
        parent::tearDown();
        if (isset($this->tmpHandle)) {
            fclose($this->tmpHandle);
        }
    }

    public function dataForLines() {
        yield ["\n"];   // *nix
        yield ["\r\n"]; // Win
        yield ["\r"];   // old Mac
    }

    /**
     * @dataProvider dataForLines
     */
    public function testLines($sep) {
        // Cases taken from http://hackage.haskell.org/package/base-4.10.0.0/docs/Prelude.html#v:lines
        // They were changed to match our criteria.
        $this->assertSame([''], lines(''));
        $this->assertSame(['', ''], lines("$sep"));
        $this->assertSame(['one'], lines("one"));
        $this->assertSame(['one', ''], lines("one$sep"));
        $this->assertSame(['one', '', ''], lines("one$sep$sep"));
        $this->assertSame(['one', 'two'], lines("one{$sep}two"));
        $this->assertSame(['one', 'two', ''], lines("one{$sep}two$sep"));
    }

    public function testWaitUntilNoOfAttempts_PredicateReturnsTrueOnSomeIteration() {
        $called = 0;
        $predicate = function () use (&$called) {
            if ($called === 4) {
                return 'abc';
            }
            $called++;
            return false;
        };

        $this->assertSame('abc', waitUntilNoOfAttempts($predicate, 0));
        $this->assertEquals(4, $called);
    }

    public function testWaitUntilNoOfAttempts_PredicateReturnsFalseAlways() {
        $called = 0;
        $predicate = function () use (&$called) {
            $called++;
            return false;
        };
        $this->expectException(RuntimeException::class, 'The number of attempts has been reached');
        waitUntilNoOfAttempts($predicate, 0);
    }

    public function testWaitUntilTimeout_Timeout() {
        $this->expectException(RuntimeException::class, 'The timeout has been reached');
        $this->assertNull(waitUntilTimeout(fn () => usleep(2000), 1000));
    }

    public function testWaitUntilTimeout_NoTimeout() {
        $this->assertSame('abc', waitUntilTimeout(fn () => 'abc', 1000));
    }

    public function testNot() {
        $fn = not(function (...$args) use (&$calledWithArgs) {
            $calledWithArgs = $args;
            return true;
        });
        $this->assertFalse($fn('foo', 'bar'));
        $this->assertEquals(['foo', 'bar'], $calledWithArgs);
    }

    public function testToAndFromJson() {
        $v = ['foo' => 'bar', 1 => new class {
            public $t = 123;
        }];
        $json = toJson($v);
        $this->assertIsString($json);
        $this->assertNotEmpty($json);
        $v1 = fromJson($json);
        $this->assertCount(count($v), $v1);
        $this->assertEquals($v['foo'], $v1['foo']);
        $this->assertEquals((array) $v[1], $v1[1]);
    }

    public function testFromJson_InvalidJsonThrowsException() {
        $this->expectException(RuntimeException::class, "Invalid JSON or too deep data");
        fromJson('S => {');
    }

    public function testNormalizeEols() {
        $this->assertEquals("foo\nbar\nbaz\n", normalizeEols("foo\r\nbar\rbaz\r\n"));
        $this->assertEquals("", normalizeEols(""));
    }

    public function testShowLn_NoArgsWritesSingleLine() {
        ob_start();
        showLn();
        $this->assertEquals("\n", ob_get_clean());
    }

    public function testShowLn_SingleArg() {
        ob_start();
        showLn("Printed");
        $this->assertEquals("Printed\n", ob_get_clean());
    }

    public function testShowLn_MultipleArgs() {
        ob_start();
        showLn("bee", "ant");
        $this->assertEquals("bee\nant\n", ob_get_clean());
    }

    public function testShowLn_ClosureGeneratorArg() {
        $gen = function () {
            foreach (['foo', 'bar', 'baz'] as $v) {
                yield $v;
            }
        };
        ob_start();
        showLn($gen);
        $this->assertEquals("foo\nbar\nbaz\n", ob_get_clean());
    }

    public function testShowLn_IterableArg() {
        $val = new ArrayIterator(['foo', 'bar', 'baz']);
        ob_start();
        showLn($val);
        $this->assertEquals("foo\nbar\nbaz\n", ob_get_clean());
    }

    public function testShorten() {
        $this->assertEquals('foo...', shorten('foobarb', 6));
        $this->assertEquals('foobar', shorten('foobar', 6));
        $this->assertEquals('fooba', shorten('fooba', 6));
        $this->assertEquals('foob', shorten('foob', 6));
        $this->assertEquals('foo', shorten('foo', 6));
        $this->assertEquals('fo', shorten('fo', 6));
        $this->assertEquals('f', shorten('f', 6));
        $this->assertEquals('', shorten('', 6));

        $this->assertEquals('foob!!', shorten('foobarb', 6, '!!'));
    }

    public function testUniqueName() {
        $this->assertEquals('unique0', uniqueName());
        $this->assertEquals('unique1', uniqueName());
    }

    public function testDeleteDups() {
        $this->assertEquals('12332', deleteDups(122332, 2));
        $this->assertEquals('aaa', deleteDups('aaa', 'b'));
        $this->assertEquals('a', deleteDups('aaa', 'a'));
    }

    public function testTrimMore() {
        $t = [
            '  ff  ',
            ' foo ' => [
                ' bar-',
            ],
        ];
        $expected = [
            'ff',
            ' foo ' => [
                'bar',
            ],
        ];
        $this->assertEquals($expected, trimMore($t, '-'));

        $this->assertEquals('ff', trimMore('__ ff  ', '_'));
    }

    public function testLast() {
        $this->assertSame('foo', last('foo', '/'));
        $this->assertSame('bar', last('foo/bar', '/'));
    }

    public function testSanitize() {
        $input = "foo[\"1][b'ar]\x00`ls`;&quot;<>";
        $this->assertEquals('foo1barlsquot', sanitize($input, '_'));
    }

    public function testClassify() {
        $this->assertEquals('Foobar', classify('foobar'));
        $this->assertEquals('Foobar', classify("&\tf\no<>o\x00`bar"));
        $this->assertEquals('FooBar', classify('foo-bar'));
        $this->assertEquals('FooBar', classify('FooBar'));
        $this->assertEquals('FooBar', classify('foo_bar'));
        $this->assertEquals('FooBar', classify('-foo-bar-'));
        $this->assertEquals('FooBar', classify('_foo-bar_'));
        $this->assertEquals('FooBar', classify('_foo-bar_'));
        $this->assertEquals('FooBar', classify('_Foo_Bar_'));
        $this->assertEquals('FooBarBaz', classify('FooBar_Baz'));
        $this->assertEquals('FooBar', classify("  foo  bar  "));
        $this->assertEquals('Foo\\Bar\\Baz', classify('foo/bar/baz'));
        $this->assertEquals('Foo\\Bar\\Baz', classify('foo\\bar\\baz'));
        $this->assertEquals('\\Foo\\Bar\\BazTest', classify('foo\\bar/baz-test', true));
    }

    public function testUnderscore() {
        $this->assertCommon('underscore');
        $this->assertEquals('foo_bar', underscore('Foo Bar'));
        $this->assertEquals('foo_bar', underscore('FooBar'));
        $this->assertEquals('foo_bar', underscore('foo-bar'));
        $this->assertEquals('foo_bar', underscore('_foo_bar_'));
        $this->assertEquals('foo_bar', underscore('-foo_bar-'));
        $this->assertEquals('_foo_bar_', underscore('-foo_bar-', false));
        $this->assertEquals('foo_bar', underscore('-Foo_Bar-'));
        $this->assertEquals('foo_bar_baz', underscore('FooBar-Baz'));
        $this->assertEquals('foo_bar', underscore("  foo  bar  "));
        $this->assertEquals('foo__bar', underscore('foo__bar'));
    }

    public function testDasherize() {
        $this->assertCommon('dasherize');
        $this->assertEquals('foo-bar', dasherize('foo-bar'));
        $this->assertEquals('foo-bar', dasherize('FooBar'));
        $this->assertEquals('foo-bar', dasherize('foo_bar'));
        $this->assertEquals('foo-bar', dasherize('-foo-bar-'));
        $this->assertEquals('foo-bar', dasherize('_foo-bar_'));
        $this->assertEquals('-foo-bar-', dasherize('_foo-bar_', '', false));
        $this->assertEquals('foo-bar', dasherize('_Foo_Bar_'));
        $this->assertEquals('foo-bar-baz', dasherize('FooBar_Baz'));
        $this->assertEquals('foo-bar', dasherize("  foo  bar  "));
        $this->assertEquals('foo-bar', dasherize('fooBar'));
        $this->assertEquals('foo--bar', dasherize('foo--bar'));
    }

    public function testCamelize() {
        $this->assertCommon('camelize');
        $this->assertEquals('fooBar', camelize('foo-bar'));
        $this->assertEquals('fooBar', camelize('FooBar'));
        $this->assertEquals('FooBar', camelize('FooBar', true));
        $this->assertEquals('FooBar', camelize('fooBar', true));
        $this->assertEquals('fooBar', camelize('foo_bar'));
        $this->assertEquals('fooBar', camelize('-foo-bar-'));
        $this->assertEquals('fooBar', camelize('_foo-bar_'));
        $this->assertEquals('fooBar', camelize('_foo-bar_'));
        $this->assertEquals('fooBar', camelize('_Foo_Bar_'));
        $this->assertEquals('fooBarBaz', camelize('FooBar_Baz'));
        $this->assertEquals('fooBar', camelize("  foo  bar  "));
    }

    public function testHumanize() {
        $this->assertEquals('v&quot;&quot;v pe te Adam bob camel ized.', humanize('v""v pe_te Adam bob camelIzed.'));
        $this->assertEquals('v""v pe te Adam bob camel ized.', humanize('v""v pe_te Adam bob camelIzed.', false));
    }

    public function testTitleize() {
        $this->assertEquals('V&quot;&quot;v Pe Te Adam Bob Camel Ized.', titleize('v""v pe_te Adam bob camelIzed.'));
        $this->assertEquals('V""v pe te Adam bob camel ized.', titleize('v""v pe_te Adam bob camelIzed.', false, false));
    }

    public function testPartial() {
        $add = function ($a, $b) {
            return $a + $b;
        };
        $add2 = partial($add, '2');
        $this->assertEquals(5, $add2(3));

        $concatenate = function ($a, $b, $c, $d, $e, $f) {
            return $a . $b . $c . $d . $e . $f;
        };
        $appendPrefix = partial($concatenate, 'foo', 'bar', 'baz');
        $this->assertEquals('foobarbazHelloWorld!', $appendPrefix('Hello', 'World', '!'));
    }

    public function testCompose_Closure() {
        $g = function ($a) {
            return 'g' . $a;
        };
        $f = function ($b) {
            return 'f' . $b;
        };
        $this->assertEquals('fghello', compose($f, $g)('hello'));
    }

    public function dataForCompose_IFnWithClosure() {
        $ifn = new class implements IFn {
            public function __invoke($value) {
                return 'IFn called ' . $value;
            }
        };
        $closure = function ($value) {
            return 'Closure called ' . $value;
        };
        return [
            [
                'IFn called Closure called test',
                $ifn,
                $closure,
            ],
            [
                'Closure called IFn called test',
                $closure,
                $ifn,
            ],
            [
                'IFn called IFn called test',
                $ifn,
                $ifn,
            ],
        ];
    }

    /**
     * @dataProvider dataForCompose_IFnWithClosure
     */
    public function testCompose_IFnWithClosure($expected, $f, $g) {
        $this->assertSame($expected, compose($f, $g)('test'));
    }

    public function testRequireFile() {
        $this->markTestIncomplete();
    }

    public function dataForQ() {
        return $this->dataForQ_("'");
    }

    /**
     * @dataProvider dataForQ
     */
    public function testWrapQ($expected, $actual) {
        $this->assertSame($expected, q($actual));
    }

    public function dataForQQ() {
        return $this->dataForQ_('"');
    }

    /**
     * @dataProvider dataForQQ
     */
    public function testQQ($expected, $actual) {
        $this->assertSame($expected, qq($actual));
    }

    public function testFormatBytes() {
        $bytes = "\x2f\xe0\xab\x00\x01\xe0";
        $this->assertEquals('\x2f\xe0\xab\x00\x01\xe0', formatBytes($bytes));
        $this->assertEquals('\x2F\xE0\xAB\x00\x01\xE0', formatBytes($bytes, '\x%02X'));
    }

    public function testFormatFloat() {
        $this->assertSame('-0.12', formatFloat(-0.1212323));
        $this->assertSame('-0.13', formatFloat(-0.1252323));
        $this->assertSame('0.00', formatFloat(0));
        $this->assertSame('0.12', formatFloat(0.1212323));
        $this->assertSame('0.13', formatFloat(0.1252323));
    }

    public function testMemoize() {
        $sum = function ($a, $b) use (&$sumCalled) {
            $sumCalled++;
            return $a + $b;
        };

        $memoizedSum = memoize($sum);

        $res = $memoizedSum(2, 3);
        $this->assertSame(1, $sumCalled);
        $this->assertSame(5, $res);

        $res = $memoizedSum(2, 3);
        $this->assertSame(1, $sumCalled);
        $this->assertSame(5, $res);

        $sub = function ($a, $b) use (&$subCalled) {
            $subCalled++;
            return $a - $b;
        };

        $memoizedSub = memoize($sub);

        $res = $memoizedSub(5, 3);
        $this->assertSame(1, $subCalled);
        $this->assertSame(2, $res);

        $res = $memoizedSub(5, 3);
        $this->assertSame(1, $subCalled);
        $this->assertSame(2, $res);
    }

    public function testMemoize_FunctionReturningNull() {
        $null = function () use (&$called) {
            $called++;
            return null;
        };
        $memoized = memoize($null);
        $this->assertNull($memoized());
        $this->assertSame(1, $called);

        $this->assertNull($memoized());
        $this->assertSame(1, $called);
    }

    public function testMemoize_DifferentArgsAfterFirstCall() {
        $sum = function ($a, $b) use (&$sumCalled) {
            $sumCalled++;
            return $a + $b;
        };

        $memoizedSum = memoize($sum);

        $res = $memoizedSum(2, 3);
        $this->assertSame(1, $sumCalled);
        $this->assertSame(5, $res);

        $this->assertSame(20, $memoizedSum(7, 13));
        $this->assertSame(2, $sumCalled);

        $this->assertSame(20, $memoizedSum(7, 13));
        $this->assertSame(2, $sumCalled);
    }

    public function testCapture() {
        $this->markTestIncomplete();
    }

    public function testTpl() {
        $code = '<?php echo "Hello $world";';
        $filePath = $this->createTmpFile();
        file_put_contents($filePath, $code);
        $this->assertSame(
            'Hello World!',
            tpl($filePath, ['world' => 'World!'])
        );
    }

    /**
     * Modified version of the providesOperator() from the https://github.com/nikic/iter
     * @Copyright (c) 2013 by Nikita Popov.
     */
    public function dataForOp() {
        return [
            ['instanceof', new stdClass, 'stdClass', true],
            ['*', 3, 2, 6],
            ['/', 3, 2, 1.5],
            ['%', 3, 2, 1],
            ['+', 3, 2, 5],
            ['-', 3, 2, 1],
            ['.', 'foo', 'bar', 'foobar'],
            ['<<', 1, 8, 256],
            ['>>', 256, 8, 1],
            ['<', 3, 5, true],
            ['<=', 5, 5, true],
            ['>', 3, 5, false],
            ['>=', 3, 5, false],
            ['==', 0, 'foo', true],
            ['!=', 1, 'foo', true],
            ['===', 0, 'foo', false],
            ['!==', 0, 'foo', true],
            ['&', 3, 1, 1],
            ['|', 3, 1, 3],
            ['^', 3, 1, 2],
            ['&&', true, false, false],
            ['||', true, false, true],
            ['**', 2, 4, 16],
            ['<=>', [0 => 1, 1 => 0], [1 => 0, 0 => 1], 0],
            ['<=>', '2e1', '1e10', -1],
            ['<=>', new stdClass(), new SplStack(), 1],
            ['<=>', new SplStack(), new stdClass(), 1],
        ];
    }

    /**
     * Modified version of the testOperator() from the https://github.com/nikic/iter
     * @Copyright (c) 2013 by Nikita Popov.
     * @dataProvider dataForOp
     */
    public function testOp($op, $a, $b, $result) {
        $fn1 = op($op);
        $fn2 = op($op, $b);

        $this->assertSame($result, $fn1($a, $b));
        $this->assertSame($result, $fn2($a));
    }

    public function testUsing_CallsInvoke() {
        $disposable = new class implements IDisposable {
            public $disposeArgs;
            public $invokeArgs;
            public function dispose(): void {
                $this->disposeArgs = func_get_args();
            }

            public function __invoke($value) {
                $this->invokeArgs = func_get_args();
                return 'returnedFromInvoke';
            }
        };
        $val = 'foo';
        $this->assertSame('returnedFromInvoke', using($disposable, $val));
        $this->assertSame([$val], $disposable->invokeArgs);
        $this->assertSame([], $disposable->disposeArgs);
    }

    public function testUsing_CallsDispose() {
        $disposable = new class implements IDisposable {
            public $disposeArgs;
            public $invokeArgs;
            public function dispose(): void {
                $this->disposeArgs = func_get_args();
            }

            public function __invoke($value) {
                throw new RuntimeException('Some error');
            }
        };
        $val = 'bar';
        try {
            using($disposable, $val);
            $this->fail();
        } catch (RuntimeException $e) {
            $this->assertSame('Some error', $e->getMessage());
        }
        $this->assertNull($disposable->invokeArgs);
        $this->assertSame([], $disposable->disposeArgs);
    }

    public function testLastPos() {
        $this->assertSame(0, lastPos('', ''));
        $this->assertSame(0, lastPos('', '', -1));
        $this->assertSame(0, lastPos('/', ''));
        $this->assertFalse(lastPos('', '/'));
        $this->assertFalse(lastPos('', '/', -1));
        $this->assertSame(0, lastPos('f', 'f'));
        $this->assertFalse(lastPos('f', 'fo'));
        $this->assertFalse(lastPos('f', 'fo', -1));
        $this->assertSame(0, lastPos('fo', 'fo', -1));
        $this->assertSame(2, lastPos('fofo', 'fo', -1));
    }

    public function testSetProps() {
        $conf = [
            'privateFoo' => 'abc',
            'protectedBar' => 'defg',
            'publicBaz' => 'hig',
        ];
        $instance = new class($conf) {
            private $privateFoo;
            protected $protectedBar;
            public $publicBaz;

            public $setPropsResult;

            public function __construct(array $conf) {
                $this->setPropsResult = setProps($this, $conf);
            }

            public function protectedBar() {
                return $this->protectedBar;
            }

            public function privateFoo() {
                return $this->privateFoo;
            }
        };


        $this->assertSame($instance, $instance->setPropsResult);
        $this->assertSame($conf['publicBaz'], $instance->publicBaz);
        $this->assertSame($conf['protectedBar'], $instance->protectedBar());
        $this->assertSame($conf['privateFoo'], $instance->privateFoo());
        $this->assertSame(['__construct', 'protectedBar', 'privateFoo'], get_class_methods($instance));
    }

    public function testSetProps_NotDeclaredProperty() {
        $instance = new class {
            private $privateFoo;
            protected $protectedBar;
            public $publicBaz;

            public function setProps($conf) {
                setProps($this, $conf);
            }
        };
        $conf = [
            'privateFoo' => 'abc',
            'protectedBar' => 'defg',
            'publicBaz' => 'hig',
            'notDeclared' => 'some',
        ];
        try {
            $instance->setProps($conf);
            $this->fail();
        } catch (UnexpectedValueException $e) {
            $this->assertStringContainsString("Unknown property 'notDeclared'", $e->getMessage());
        }
        $this->assertFalse(property_exists($instance, 'notDeclared'));
    }

    public function dataForIt_ValidCases() {
        $it = new ArrayIterator(['foo', 'bar']);
        $itAggr = new class($it) implements IteratorAggregate {
            public $it;

            public function __construct($it) {
                $this->it = $it;
            }

            public function getIterator() {
                return $this->it;
            }
        };
        yield [
            $itAggr->it,
            $itAggr
        ];
        yield [
            function ($actual) {
                $this->assertInstanceOf(Generator::class, $actual);
            },
            function () {
                yield 'foo';
                yield 'bar';
            }
        ];
        yield [
            ['foo', 'bar'],
            ['foo', 'bar'],
        ];
        yield [
            $it,
            $it,
        ];
    }

    /**
     * @dataProvider dataForIt_ValidCases
     */
    public function testIt_ValidCases($expected, $it) {
        if ($expected instanceof Closure) {
            $expected(it($it));
        } else {
            $this->assertSame($expected, it($it));
        }
    }

    public function dataForIt_InvalidCases() {
        yield ['foo'];
        yield [function () {}];
    }

    /**
     * @dataProvider dataForIt_InvalidCases
     */
    public function testIt_InvalidCases($it) {
        $this->expectException(UnexpectedValueException::class);
        it($it);
    }

    public function testWords() {
        $this->assertSame([], words(''));
        $this->assertSame([], words('    '));
        $this->assertSame(['foo'], words(' foo   '));
        $this->assertSame(['foo', 'bar'], words(' foo   bar    '));
        $this->assertSame(['foo', 'bar'], words('foo   bar'));
        $this->assertSame(['bar'], words('bar'));
        $this->assertSame(['bar', '123'], words('bar    123   '));
        $this->assertSame(['123'], words(123));

        $this->assertSame(['foo'], words('foo', 2));
        $this->assertSame(['foo', 'bar baz'], words('foo bar baz', 2));
    }

    public function testUcfirst() {
        $this->assertSame('', ucfirst(''));
        $this->assertSame('Foo', ucfirst('foo'));
        $this->assertSame('Тест', ucfirst('тест'));
    }

    public function testIndent_Uindent() {
        $orig = <<<OUT
begin
        Bar
            baz
    Some text
end
OUT;
        $expected = <<<OUT
    begin
            Bar
                baz
        Some text
    end
OUT;
        $actual = indent($orig);
        $this->assertSame($expected, $actual);
        $this->assertSame($orig, unindent($actual));
    }

    private function assertCommon($fn) {
        $fn = 'Morpho\Base\\' . $fn;
        $this->assertEquals('foobar', call_user_func($fn, 'foobar'));
        $this->assertEquals('foobar', call_user_func($fn, "&\tf\no<>o\x00`bar"));
    }

    private function dataForQ_(string $quote) {
        return [
            [
                "$quote$quote",
                '',
            ],
            [
                "{$quote}123{$quote}",
                123,
            ],
            [
                "{$quote}Hello{$quote}",
                'Hello'
            ],
            [
                [
                    "{$quote}foo{$quote}",
                    "{$quote}bar{$quote}",
                ],
                [
                    'foo',
                    'bar'
                ],
            ],
            [
                [
                    'a' => "{$quote}foo{$quote}",
                    'b' => "{$quote}bar{$quote}",
                ],
                [
                    'a' => 'foo',
                    'b' => 'bar',
                ],
            ],
        ];
    }
}
