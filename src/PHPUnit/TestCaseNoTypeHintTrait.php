<?php

namespace Laminas\Test\PHPUnit;

use function method_exists;

/**
 * @internal
 */
trait TestCaseNoTypeHintTrait
{
    protected function setUp()
    {
        if (method_exists($this, 'setUpCompat')) {
            $this->setUpCompat();
        }
    }

    protected function tearDown()
    {
        if (method_exists($this, 'tearDownCompat')) {
            $this->tearDownCompat();
        }
    }

    public static function setUpBeforeClass()
    {
        if (method_exists(static::class, 'setUpBeforeClassCompat')) {
            static::setUpBeforeClassCompat();
        }
    }

    public static function tearDownAfterClass()
    {
        if (method_exists(static::class, 'tearDownAfterClassCompat')) {
            static::tearDownAfterClassCompat();
        }
    }
}
