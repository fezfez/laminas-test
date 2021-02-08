<?php

/**
 * @see       https://github.com/laminas/laminas-test for the canonical source repository
 * @copyright https://github.com/laminas/laminas-test/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-test/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Test\PHPUnit\Util;

use Laminas\ModuleManager\Exception\RuntimeException;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\ApplicationInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use laminas\test\phpunit\testcasetrait;
use Laminas\Test\Util\ModuleLoader;
use LaminasTest\Test\ExpectedExceptionTrait;
use PHPUnit\Framework\TestCase;

use function array_diff;
use function is_dir;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function unlink;

class ModuleLoaderTest extends TestCase
{
    use ExpectedExceptionTrait;
    use testcasetrait;

    /** @return void */
    public function tearDownCacheDir()
    {
        $cacheDir = sys_get_temp_dir() . '/laminas-module-test';
        if (is_dir($cacheDir)) {
            static::rmdir($cacheDir);
        }
    }

    /** @param string $dir */
    public static function rmdir($dir): bool
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            is_dir("$dir/$file") ? static::rmdir("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }

    /** @return void */
    protected function setUpCompat()
    {
        $this->tearDownCacheDir();
    }

    /** @return void */
    protected function tearDownCompat()
    {
        $this->tearDownCacheDir();
    }

    /** @return void */
    public function testCanLoadModule()
    {
        require_once __DIR__ . '/../../_files/Baz/Module.php';

        $loader = new ModuleLoader(['Baz']);
        $baz    = $loader->getModule('Baz');
        $this->assertInstanceOf('Baz\Module', $baz);
    }

    /** @return void */
    public function testCanNotLoadModule()
    {
        $this->expectedException(RuntimeException::class, 'could not be initialized');
        $loader = new ModuleLoader(['FooBaz']);
    }

    /** @return void */
    public function testCanLoadModuleWithPath()
    {
        $loader = new ModuleLoader(['Baz' => __DIR__ . '/../../_files/Baz']);
        $baz    = $loader->getModule('Baz');
        $this->assertInstanceOf('Baz\Module', $baz);
    }

    /** @return void */
    public function testCanLoadModules()
    {
        require_once __DIR__ . '/../../_files/Baz/Module.php';
        require_once __DIR__ . '/../../_files/modules-path/with-subdir/Foo/Module.php';

        $loader = new ModuleLoader(['Baz', 'Foo']);
        $baz    = $loader->getModule('Baz');
        $this->assertInstanceOf('Baz\Module', $baz);
        $foo = $loader->getModule('Foo');
        $this->assertInstanceOf('Foo\Module', $foo);
    }

    /** @return void */
    public function testCanLoadModulesWithPath()
    {
        $loader = new ModuleLoader([
            'Baz' => __DIR__ . '/../../_files/Baz',
            'Foo' => __DIR__ . '/../../_files/modules-path/with-subdir/Foo',
        ]);

        $fooObject = $loader->getServiceManager()->get('FooObject');
        $this->assertInstanceOf('stdClass', $fooObject);
    }

    /** @return void */
    public function testCanLoadModulesFromConfig()
    {
        $config = include __DIR__ . '/../../_files/application.config.php';
        $loader = new ModuleLoader($config);
        $baz    = $loader->getModule('Baz');
        $this->assertInstanceOf('Baz\Module', $baz);
    }

    /** @return void */
    public function testCanGetService()
    {
        $loader = new ModuleLoader(['Baz' => __DIR__ . '/../../_files/Baz']);

        $this->assertInstanceOf(
            ServiceLocatorInterface::class,
            $loader->getServiceManager()
        );
        $this->assertInstanceOf(
            ModuleManager::class,
            $loader->getModuleManager()
        );
        $this->assertInstanceOf(
            ApplicationInterface::class,
            $loader->getApplication()
        );
    }
}
