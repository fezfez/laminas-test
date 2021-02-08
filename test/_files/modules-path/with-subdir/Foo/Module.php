<?php

/**
 * @see       https://github.com/laminas/laminas-test for the canonical source repository
 * @copyright https://github.com/laminas/laminas-test/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-test/blob/master/LICENSE.md New BSD License
 */

namespace Foo;

use Laminas\Loader\StandardAutoloader;
use stdClass;

class Module
{
    /** @return array */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /** @return array */
    public function getAutoloaderConfig()
    {
        return [
            StandardAutoloader::class => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    /** @return array */
    public function getServiceConfig()
    {
        return [
            // Legacy Zend Framework aliases
            'aliases'   => [],
            'factories' => [
                'FooObject' => function ($sm) {
                    return new stdClass();
                },
            ],
        ];
    }
}
