<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit51324590b4ddfa2a7529bc7d065cb030
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'TimAlexander\\Aisearch\\' => 22,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'TimAlexander\\Aisearch\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit51324590b4ddfa2a7529bc7d065cb030::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit51324590b4ddfa2a7529bc7d065cb030::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit51324590b4ddfa2a7529bc7d065cb030::$classMap;

        }, null, ClassLoader::class);
    }
}
