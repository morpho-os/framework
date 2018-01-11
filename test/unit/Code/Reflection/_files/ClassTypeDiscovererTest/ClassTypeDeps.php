<?php
namespace Morpho\Qa\Test\Unit\Code\Reflection\ClassTypeDiscovererTest;

class First extends A_ClassExtends implements B_ClassImplementsA, B_ClassImplementsB {
    use C_ClassUsesTrait;

    const PING_PONG = 123;
    private static $pingPong;

    public function __construct() {
        new D_InstantiatesNewObject();

        E_CallsMethodStatically::some();

        if (F_ReadsStaticProperty::$foo) {

        }
        G_WritesStaticProperty::$foo = 'bar';

        try {
        } catch (H_CatchesExceptionA $e) {
        } catch (H_CatchesExceptionB $e) {
        }

        $v = '123';
        if ($v instanceof I_AppliesInstanceOfOperator) {

        }

        if (J_ReadsClassConstant::SOME) {

        }

        new class extends R_AnonymousClassExtends implements S_AnonymousClassImplementsA, S_AnonymousClassImplementsB {
        };

        function ($a, T_AnonymousFunctionDefinitionHasParameterWithType $b): U_AnonymousFunctionDefinitionHasReturnType {

        };
    }

    public function skipMe() {
        // The next statements should not be included in result.
        self::doSomething();
        self::PING_PONG;
        self::$pingPong = '456';

        static::doSomething();
        static::PING_PONG;

        static::$pingPong = '456';

        $t = new self();
        $t::PING_PONG;

        $t::doSomething();

        $t = new static();
        $t::PING_PONG;
    }

    public function foo(int $a, K_MethodDefinitionHasParameterWithType $b, self $c, $d = self::PING_PONG): L_MethodDefinitionHasReturnType {

    }

    public function bar(): string {
        return '123';
    }

    public static function doSomething() {
    }
}

function test(int $a, M_FunctionDefinitionHasParameterWithType $b): N_FunctionDefinitionHasReturnType {
    
}

class Second {
    public function __construct(string $a, O_ConstructorDefinitionHasParameterWithType $b) {

    }
}

interface IThird extends P_ExtendsInterfaceA, P_ExtendsInterfaceB {
}

trait TFourth {
    use Q_TraitUsesTrait;
}
