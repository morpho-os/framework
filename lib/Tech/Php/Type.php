<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Php;

/**
 * Enum for PHP types.
 */
abstract class Type {
    /*
    PHP types can be used in:
    * Property definition
    * Formal parameter type hint
    * Return type hint
    * Class name or interface name

    E.g.:
        class TClassOrInterface {
            public TProperty $foo;
        }
        function foo(TParam $bar): TReturn {
            ...
        }
    Where:
        TClassOrInterface: ClassName | InterfaceName
            Any class and interface name.

        TProperty: int | float | bool | string | array | object | iterable | self | parent | mixed | TClassOrInterface | TUnion
            Can be used in class definition as property type hint.
            todo: How to deal with `null` in `mixed` for TProperty?

        TParam: TProperty | static | callable
            Can be used in function definition as formal parameter type hint.

        TReturn: TParam | void
            Can be used in function definition as return type hint.

        TScalar: int | float | bool | string | null
            Scalar type.

        TUnion: TScalar | array | object | ...todo: clarify types which can be used in union
            Types which can be used in union, e.g. `int | bool`.

        TNullable: int | float | bool | string | array | object | TClassOrInterface
            Some types can be nullable, i.e. null is valid value for the type, e.g. ?string = null

        TSpecial: resource
            Special type, used only in PHP documentation.

        mixed
            Means any type, the same as disabling type checking?

        iterable: array | Traversable

        callable: [$obj, 'method'] | Closure | todo... static methods
            Any value which can be called with `call_user_func($val)`
    */
    public const INT      = 'int';         // in TProperty, TParam, TReturn, TScalar, TNullable
    public const FLOAT    = 'float';       // in TProperty, TParam, TReturn, TScalar, TNullable
    public const BOOL     = 'bool';        // in TProperty, TParam, TReturn, TScalar, TNullable
    public const STRING   = 'string';      // in TProperty, TParam, TReturn, TScalar, TNullable
    public const NULL     = 'null';        // in TScalar
    public const ARRAY    = 'array';       // in TProperty, TParam, TReturn, TUnion, TNullable
    public const RESOURCE = 'resource';    // in TSpecial
    public const OBJECT   = 'object';      // in TProperty, TParam, TReturn, TUnion, TNullable

    // todo vvv:
    public const ITERABLE = 'iterable';    // in TProperty, TParam, TReturn
    public const CALLABLE = 'callable';    // in TParam, TNullable, TReturn
    public const SELF     = 'self';        // in TProperty, TParam, TReturn
    public const PUBLIC   = 'parent';      // in TProperty, TParam, TReturn
    public const STATIC   = 'static';      // in TParam, TReturn
    public const VOID     = 'void';        // in TReturn
    public const MIXED    = 'mixed';       // in TProperty, TParam, TReturn
    //public const CLASS_OR_INTERFACE = 'classin -interface'; // TProperty, TParam, TReturn, TClassOrInterface
    //public const UNION = 'union';        // in TProperty, TParam, TReturn
}