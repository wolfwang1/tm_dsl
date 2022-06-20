<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitaa8816ff86edce29ddf6ca06b7d92717
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'Workerman\\MySQL\\' => 16,
            'Workerman\\' => 10,
        ),
        'G' => 
        array (
            'GlobalData\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Workerman\\MySQL\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/mysql/src',
        ),
        'Workerman\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/workerman',
        ),
        'GlobalData\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/globaldata/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitaa8816ff86edce29ddf6ca06b7d92717::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitaa8816ff86edce29ddf6ca06b7d92717::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
