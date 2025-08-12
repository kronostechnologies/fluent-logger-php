<?php
/**
 */

namespace Fluent\Logger;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

function fluentTests_FluentLogger_DummyFunction()
{
}

class BaseLoggerTest extends TestCase
{
    #[Test]
    public function testRegisterErrorHandler()
    {
        $errorHandler = $this->getErrorHandlerProvider();

        $base = $this->createMock(FluentLogger::class);
        $this->assertTrue($base->registerErrorHandler($errorHandler));
    }

    #[Test]
    public function testRegisterInvalidErrorHandler()
    {
        $errorHandler = $this->getInvalidErrorHandlerProvider();

        $base = $this->createMock(FluentLogger::class);
        $this->expectException(\InvalidArgumentException::class);
        $base->registerErrorHandler($errorHandler);
    }

    #[Test]
    public function testUnregisterErrorHandler()
    {
        $base = $this->createMock(FluentLogger::class);
        $prop = new \ReflectionProperty($base, 'error_handler');
        $prop->setAccessible(true);

        $base->registerErrorHandler(function() {});
        $this->assertNotNull($prop->getValue($base));

        $base->unregisterErrorHandler();
        $this->assertNull($prop->getValue($base));
    }

    private function getErrorHandlerProvider()
    {
        return array(
            array(
                'FluentTests\FluentLogger\fluentTests_FluentLogger_DummyFunction'
            ),
            array(
                array($this, 'errorHandlerProvider')
            ),
            array(
                function () {
                }, // closure
            ),
        );
    }

    private function getInvalidErrorHandlerProvider()
    {
        return array(
            array(
                null,
            ),
            array(
                array($this, 'errorHandlerProvider_Invalid') // not exists
            ),
            array(
                array($this, 'errorHandlerProvider_Invalid', 'hoge') // invalid
            ),
        );
    }
}
