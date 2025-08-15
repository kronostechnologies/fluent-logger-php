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
        $testCases = $this->getErrorHandlerProviders();

        foreach ($testCases as $description => $errorHandler) {
            $base = new FluentLogger("localhost");
            $this->assertTrue($base->registerErrorHandler($errorHandler), "Failed for: " . $description);
        }
    }

    #[Test]
    public function testRegisterInvalidErrorHandler()
    {
        $testCases = $this->getInvalidErrorHandlerProviders();

        foreach ($testCases as $description => $errorHandler) {
            $this->expectException(\InvalidArgumentException::class);
            $base = new FluentLogger("localhost");
            $this->assertTrue($base->registerErrorHandler($errorHandler));
        }
    }

    #[Test]
    public function testUnregisterErrorHandler()
    {
        $base = new FluentLogger("localhost");
        $prop = new \ReflectionProperty($base, 'error_handler');
        $prop->setAccessible(true);

        $base->registerErrorHandler(function() {});
        $this->assertNotNull($prop->getValue($base));

        $base->unregisterErrorHandler();
        $this->assertNull($prop->getValue($base));
    }

    public function getErrorHandlerProviders(): array
    {
        return [
            'Fluent\Logger\fluentTests_FluentLogger_DummyFunction',
            array($this, 'getErrorHandlerProviders'),
            function () {
            }, // closure
        ];
    }

    public function getInvalidErrorHandlerProviders(): array
    {
        return array(
            null,
            array($this, 'errorHandlerProvider_Invalid'), // not exists
            array($this, 'errorHandlerProvider_Invalid', 'hoge') // invalid
        );
    }
}
