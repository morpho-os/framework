<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Js;

use Morpho\Base\Config;

class TscConfig extends Config {
    // Default module kind
    public const MODULE_KIND = 'amd';

    // See https://www.typescriptlang.org/docs/handbook/compiler-options.html
    protected $default = [
        'allowJs' => true,
        // @TODO: "allowSyntheticDefaultImports": true,
        'alwaysStrict' => true,
        'experimentalDecorators' => true,
        'forceConsistentCasingInFileNames' => true,
        'jsx' => 'preserve',
        'module' => self::MODULE_KIND,
        'moduleResolution' => 'node',
        'newLine' => 'lf',
        'noEmitOnError' => true,
        'noImplicitAny' => true,
        'noImplicitReturns' => true,
        'noImplicitThis' => true,
        'noUnusedLocals' => false,
        'preserveConstEnums' => true,
        //'pretty' => true,
        'removeComments' => true,
        'sourceMap' => true,
        'strictNullChecks' => false,
        'target' => 'es5',
        "lib" => ["dom", "es2015.promise", "es2015.iterable", "es5"],
    ];
}