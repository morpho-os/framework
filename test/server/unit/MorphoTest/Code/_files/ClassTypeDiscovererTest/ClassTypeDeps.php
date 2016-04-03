<?php
namespace MorphoTest\Code\ClassTypeDiscovererTest;

class ClassTypeDepsTest extends A_Extends implements B_Implements, C_Implements {
    use D_Uses;

    public function __construct() {
        new E_Instantiates();

        F_CallsStatically::some();

        if (G_ReadsStaticProperty::$foo) {

        }
        H_WritesStaticProperty::$foo = 'bar';

        try {
        } catch (I_CatchesException $e) {
        } catch (J_CatchesException $e) {
        }

        $v = '123';
        if ($v instanceof K_AppliesInstanceOfOperator) {

        }

        if (L_ReadsClassConstant::SOME) {

        }
    }

    public function foo(int $a, M_ClassMethodDeclaresParameterWithType $b): N_ClassMethodDeclaresReturnType {

    }

    public function bar(): string {
        return '123';
    }
}