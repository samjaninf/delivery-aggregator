<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2d67c90242218b8ac121378b7444508d
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'Automattic\\WooCommerce\\' => 23,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Automattic\\WooCommerce\\' => 
        array (
            0 => __DIR__ . '/..' . '/automattic/woocommerce/src/WooCommerce',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2d67c90242218b8ac121378b7444508d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2d67c90242218b8ac121378b7444508d::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
