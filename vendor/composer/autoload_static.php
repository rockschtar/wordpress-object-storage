<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5064cd8addc82ce67d8889ab9ae290bc
{
    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'Rockschtar\\WordPress\\ObjectStorage\\Tests\\' => 41,
            'Rockschtar\\WordPress\\ObjectStorage\\' => 35,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Rockschtar\\WordPress\\ObjectStorage\\Tests\\' => 
        array (
            0 => __DIR__ . '/../..' . '/tests',
        ),
        'Rockschtar\\WordPress\\ObjectStorage\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5064cd8addc82ce67d8889ab9ae290bc::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5064cd8addc82ce67d8889ab9ae290bc::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
