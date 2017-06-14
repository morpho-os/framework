<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdf62c53f3802c83f6b707e042f2e096d
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Morpho\\User\\Domain\\' => 19,
            'Morpho\\User\\Controller\\' => 23,
            'Morpho\\User\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Morpho\\User\\Domain\\' => 
        array (
            0 => __DIR__ . '/../..' . '/domain',
        ),
        'Morpho\\User\\Controller\\' => 
        array (
            0 => __DIR__ . '/../..' . '/controller',
        ),
        'Morpho\\User\\' => 
        array (
            0 => __DIR__ . '/../..' . '/lib',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitdf62c53f3802c83f6b707e042f2e096d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitdf62c53f3802c83f6b707e042f2e096d::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
