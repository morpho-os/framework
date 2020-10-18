<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Base;

use Morpho\Testing\TestCase;
use Morpho\Base\{Result, Ok, Err, IContainer, IMonad, IFunctor, IChain, IApplicative, IApply};

class ResultTest extends TestCase {
    public function dataForInterface() {
        yield [new Ok()];
        yield [new Err()];
    }
    /**
     * @dataProvider dataForInterface
     */
    public function testInterface($instance) {
        $this->assertInstanceOf(IFunctor::class, $instance);
        $this->assertInstanceOf(IApply::class, $instance);
        $this->assertInstanceOf(IChain::class, $instance);
        $this->assertInstanceOf(IApplicative::class, $instance);
        $this->assertInstanceOf(IMonad::class, $instance);
        $this->assertInstanceOf(Result::class, $instance);
    }

    public function testChain_Ok() {
        $ok1Val = 'foo';
        $ok2Val = 'bar';
        $res = (new Ok($ok1Val))->chain(function ($val) use (&$captured, $ok2Val) {
            $captured = $val;
            return new Ok($ok2Val);
        });
        $this->assertSame($ok1Val, $captured);
        $this->assertEquals(new Ok($ok2Val), $res);
    }

    public function testChain_Err() {
        $okVal = 'foo';
        $errVal = 'bar';
        $res = (new Ok($okVal))->chain(function ($val) use (&$captured, $errVal) {
            $captured = $val;
            return new Err($errVal);
        });
        $this->assertSame($okVal, $captured);
        $this->assertEquals(new Err($errVal), $res);
    }

    public function dataForComposition() {
        $req = [
                'name' => "Phillip",
                'email' => "phillip@example",
        ];
        yield [
            $req,
            new Ok($req),
        ];

        $req = [
            'name' => 'Phillip',
            'email' => "phillip@localhost",
        ];
        yield [
            $req,
            new Err('No email from localhost is allowed.'),
        ];
    }

    /**
     * @dataProvider dataForComposition
     */
    public function testComposition($req, $expected) {
        // Adopted from https://docs.microsoft.com/en-us/dotnet/fsharp/language-reference/results

        $validateName = function ($req): Result {
            if ($req['name'] === null) {
                return new Err('No name found.');
            }
            if ($req['name'] === '') {
                return new Err('Name is empty.');
            }
            if ($req['name'] === 'bananas') {
                return new Err('Bananas is not a name.');
            }
            return new Ok($req);
        };

        $validateEmail = function ($req) {
            if ($req['email'] === null) {
                return new Err('No email found.');
            }
            if ($req['email'] === '') {
                return new Err('Email is empty.');
            }
            if (\substr($req['email'], -\strlen('localhost')) === 'localhost') {
                return new Err("No email from localhost is allowed.");
            }
            return new Ok($req);
        };

        $validateRequest = function (Result $reqResult) use ($validateName, $validateEmail, $expected): Result {
            return $reqResult->chain($validateName)
                             ->chain($validateEmail)
                             ->chain(function ($val) use ($expected) {
                                 if ($expected instanceof Err) {
                                     throw new \RuntimeException("Must not be called");
                                 }
                                 return new Ok($val);
                             });
        };

        $res1 = $validateRequest(new Ok($req));
        $this->assertEquals($expected, $res1);
    }

    public function testVal() {
        $this->assertTrue((new Ok())->val());
        $this->assertFalse((new Err())->val());
        $this->assertSame(3, (new Ok(3))->val());
        $this->assertSame(4, (new Err(4))->val());
    }

    public function testMonadLaws_LeftIdentity() {
        // M.chain(f, M.of(a)) ≡ f(a)
        $fn = function ($v) {
            return new Ok($v);
        };
        $val = 'abc';
        $this->assertEquals(
            $fn($val),
            (new Ok($val))->chain($fn),
        );
    }

    public function testMonadLaws_RightIdentity() {
        // M.chain(M.of, u) ≡ u
        $fn = function ($v) {
            return new Ok($v);
        };
        $this->assertEquals(
            new Ok('abc'),
            (new Ok('abc'))->chain($fn)
        );
    }

    public function testMonadLaws_Associativity() {
        // chain: <a, b>(a => T<b>, T<a>) => T<b>
        // M.chain(g, M.chain(f, u)) ≡ M.chain(x => M.chain(g, f(x)), u)
        $f = function ($v) {
            return new Ok($v * 4);
        };
        $g = function ($v) {
            return new Ok($v * 3);
        };

        $this->assertEquals(
            (new Ok(5))->chain(fn ($x) => $f($x)->chain($g)),
            (new Ok(5))->chain($f)->chain($g)
        );
    }

    public function testMap() {
        $res = (new Ok(2))->map(fn ($val)=> $val - 3);
        $this->assertInstanceOf(IFunctor::class, $res);
        $this->assertSame(-1, $res->val());
    }
}
