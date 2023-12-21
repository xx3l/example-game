<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitcc64b9847597a8a8fd4e8e8866374ff7
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'Workerman\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Workerman\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/workerman',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitcc64b9847597a8a8fd4e8e8866374ff7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitcc64b9847597a8a8fd4e8e8866374ff7::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitcc64b9847597a8a8fd4e8e8866374ff7::$classMap;

        }, null, ClassLoader::class);
    }
}
