<?php

declare(strict_types=1);

$cacheDir = sys_get_temp_dir() . '/laminas-module-test';
if (! is_dir($cacheDir)) {
    mkdir($cacheDir);
}

return [
    'modules'                 => [
        'Laminas\Router',
        'Laminas\Validator',
        'Laminas\Mvc\Plugin\FlashMessenger',
        'Baz',
    ],
    'module_listener_options' => [
        'config_cache_enabled' => true,
        'cache_dir'            => $cacheDir,
        'config_cache_key'     => 'phpunit',
        'config_static_paths'  => [],
        'module_paths'         => [
            'Baz' => __DIR__ . '/Baz/',
        ],
    ],
];
