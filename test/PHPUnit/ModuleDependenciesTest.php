<?php

namespace LaminasTest\Test\PHPUnit;

use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use LaminasTest\Test\ExpectedExceptionTrait;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @group      Laminas_Test
 */
class ModuleDependenciesTest extends AbstractHttpControllerTestCase
{
    use ExpectedExceptionTrait;

    public function testDependenciesModules()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../_files/application.config.with.dependencies.php'
        );
        $sm = $this->getApplicationServiceLocator();
        $this->assertEquals(true, $sm->has('FooObject'));
        $this->assertEquals(true, $sm->has('BarObject'));

        $this->assertModulesLoaded(['Foo', 'Bar']);
        $this->expectedException(ExpectationFailedException::class);
        $this->assertModulesLoaded(['Foo', 'Bar', 'Unknow']);
    }

    public function testBadDependenciesModules()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../_files/application.config.with.dependencies.disabled.php'
        );
        $sm = $this->getApplicationServiceLocator();
        $this->assertEquals(false, $sm->has('FooObject'));
        $this->assertEquals(true, $sm->has('BarObject'));

        $this->assertNotModulesLoaded(['Foo']);
        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotModulesLoaded(['Foo', 'Bar']);
    }
}
