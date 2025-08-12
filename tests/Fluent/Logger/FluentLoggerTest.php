<?php

namespace Fluent\Logger;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FluentLoggerTest extends TestCase
{
    const TAG          = 'debug.test';
    const OBJECT_KEY   = 'hello';
    const OBJECT_VALUE = 'world';

    public function tearDown(): void
    {
        FluentLogger::clearInstances();
    }

    /**
     * Post will return true in the case of posting successfully
     */
    #[Test]
    public function testPostWillReturnTrueInTheCaseOfPostingSuccessfully()
    {
        $socket = fopen("php://memory", "a+");

        /* localhost is dummy string. we set php://memory as a socket */
        $logger     = FluentLogger::open("localhost");
        $reflection = new \ReflectionProperty("Fluent\Logger\FluentLogger", "socket");
        $reflection->setAccessible(true);
        $reflection->setValue($logger, $socket);

        $this->assertTrue($logger->post(self::TAG, array("foo" => "bar")), "Post method returned boolean");

        return $socket;
    }

    /**
     * @depends testPostWillReturnTrueInTheCaseOfPostingSuccessfully
     */
//    public function testPostedStringIsJson($socket)
//    {
//        fseek($socket, 0);
//        $actual = "";
//        while ($string = fread($socket, 1024)) {
//            $actual .= $string;
//        }
//        $this->assertStringMatchesFormat('["debug.test",%d,{"foo":"bar"}]', $actual);
//    }

    /**
     *  fwrite on read only memory returns zero
     *  HHVM has a bug which returns a positive integer
     *  see https://github.com/facebook/hhvm/issues/5187
     */
    #[Test]
    #[Depends('testPostWillReturnTrueInTheCaseOfPostingSuccessfully')]
    public function testFwriteOnReadonlyMemoryRreturnsZero()
    {
        $n = fwrite(fopen("php://memory", "r"), "hello");
        $this->assertEquals(0, $n, "fwrite on ROM returns 0");
    }


    /**
     * Post will return false in the case of posting unsuccessfully by reached max retry count
     */
    #[Test]
    public function testPostWillReturnFalseInTheCaseOfPostingUnsuccessfullyByReachedMaxRetryCount()
    {
        /* localhost is dummy string. we set php://memory as a socket */
        $logger = FluentLogger::open("localhost");
        $this->setSocket($logger, fopen("php://memory", "r"));

        $this->assertFalse($logger->post(self::TAG, array("foo" => "bar")), "Post method returned boolean");
    }

    /**
     * Post will return false in the case of posting unsuccessfully by writing failed
     */
    #[Test]
    public function testPostWillReturnFalseInTheCaseOfPostingUnsuccessfullyByWritingFailed()
    {
        $logger = $this->getMockOfLogger();
        $logger->expects($this->any())->method("write")->willReturn(false);
        $this->setSocket($logger, fopen("php://memory", "a+"));

        $this->assertFalse($logger->post(self::TAG, array("foo" => "bar")), "Post method returned boolean");
    }

    /**
     * Post will return false in the case of posting unsuccessfully by connection aborted
     */
    #[Test]
    public function testPostWillReturnFalseInTheCaseOfPostingUnsuccessfullyByConnectionAborted()
    {
        $logger = $this->getMockOfLogger();
        $logger->expects($this->any())->method("write")->willReturn("");
        $this->setSocket($logger, fopen("php://memory", "a+"));

        $this->assertFalse($logger->post(self::TAG, array("foo" => "bar")), "Post method returned boolean");
    }

    private function setSocket($logger, $socket)
    {
        $reflection = new \ReflectionProperty("Fluent\Logger\FluentLogger", "socket");
        $reflection->setAccessible(true);
        $reflection->setValue($logger, $socket);
    }

    private function getMockOfLogger()
    {
        return $this->getMockBuilder(FluentLogger::class)
            ->onlyMethods(["write"])
            ->setConstructorArgs(["localhost"])
            ->getMock();
    }

    #[Test]
    #[DataProvider('providesTransport')]
    public function testGetTransportUri($host, $port, $expected_uri, $error_msg)
    {
        $actual_uri = FluentLogger::getTransportUri($host, $port);
        $this->assertEquals($expected_uri, $actual_uri, $error_msg);
    }

    public static function providesTransport()
    {
        return array(
            array("localhost", 8080, "tcp://localhost:8080", "unexpected uri returns"),
            array("127.0.0.1", 8080, "tcp://127.0.0.1:8080", "unexpected uri returns"),
            array("tcp://localhost", 8080, "tcp://localhost:8080", "unexpected uri returns"),
            array("tcp://127.0.0.1", 8080, "tcp://127.0.0.1:8080", "unexpected uri returns"),
            array("unix:///var/fluentd", 0, "unix:///var/fluentd", "unexpected uri returns"),
            array("unix:///var/fluentd", 8080, "unix:///var/fluentd", "unix domain uri have to ignores port number"),
            array("fe80::1", 8080, "tcp://[fe80::1]:8080", "ipv6 support failed"),
            array("tcp://fe80::1", 8081, "tcp://[fe80::1]:8081", "ipv6 support failed"),
            array("tcp://[fe80::1]", 8082, "tcp://[fe80::1]:8082", "ipv6 support failed"),
        );
    }

    #[Test]
    public function testGetTransportUriCauseException()
    {
        try {
            FluentLogger::getTransportUri("udp://localhost", 1192);
            $this->fail("getTransportUri does not thorow exception");
        } catch (\Exception $e) {
            $this->assertInstanceOf("\\Exception", $e);
        }
    }

    #[Test]
    public function testSetPacker()
    {
        $logger = new FluentLogger("localhost");
        $packer = new \Fluent\Logger\JsonPacker();

        $prop = new \ReflectionProperty($logger, "packer");
        $prop->setAccessible(true);
        $logger->setPacker($packer);
        $this->assertSame($packer, $prop->getValue($logger), "unexpected packer was set");
    }

    #[Test]
    public function testGetPacker()
    {
        $logger = new FluentLogger("localhost");

        $this->assertInstanceOf("Fluent\\Logger\\PackerInterface", $logger->getPacker(), "testGetPacker returns unexpected packer");
    }

    #[Test]
    public function testClearInstances()
    {
        $prop = new \ReflectionProperty(FluentLogger::class, "instances");
        $prop->setAccessible(true);

        FluentLogger::open("localhost", 1191);
        FluentLogger::open("localhost", 1192);
        $this->assertCount(2, $prop->getValue());

        FluentLogger::clearInstances();
        $this->assertCount(0, $prop->getValue());
    }

    #[Test]
    public function testMergeOptions()
    {
        $logger = new FluentLogger("localhost");
        $prop   = new \ReflectionProperty($logger, "options");
        $prop->setAccessible(true);

        $default = $prop->getValue($logger);

        $additional_options = array("socket_timeout" => 10);
        $logger->mergeOptions($additional_options);
        $this->assertEquals(array_merge($default, $additional_options), $prop->getValue($logger), "mergeOptions looks wired");
    }

    #[Test]
    public function testMergeOptionsThrowsException()
    {
        $logger             = new FluentLogger("localhost");
        $additional_options = array("unexpected_key" => 10);
        try {
            $logger->mergeOptions($additional_options);
            $this->fail("mergeOptions doesn't thorw Exception");
        } catch (\Exception $e) {
            $this->assertInstanceOf("\\Exception", $e);
        }

    }

    #[Test]
    public function testSetOptions()
    {
        $logger = new FluentLogger("localhost");
        $prop   = new \ReflectionProperty($logger, "options");
        $prop->setAccessible(true);

        $additional_options = array("socket_timeout" => 10);
        $logger->setOptions($additional_options);
        $this->assertEquals($additional_options, $prop->getValue($logger), "setOptions looks wired");
    }

    #[Test]
    public function testConnect()
    {
        $logger = new FluentLogger("localhost", 119223);
        $method = new \ReflectionMethod($logger, "connect");
        $method->setAccessible(true);
        try {
            $method->invoke($logger);
            $this->fail("mergeOptions doesn't thorw Exception");
        } catch (\Exception $e) {
            $this->assertInstanceOf("\\Exception", $e);
        }
    }

    #[Test]
    public function testGetOption()
    {
        $logger = new FluentLogger("localhost", 119223);
        $this->assertEquals(FluentLogger::CONNECTION_TIMEOUT, $logger->getOption("socket_timeout"),
            "getOptions retunrs unexpected value");
    }

    #[Test]
    public function testReconnect()
    {
        $logger = new FluentLogger("localhost", 119223);
        $method = new \ReflectionMethod($logger, "reconnect");
        $method->setAccessible(true);
        try {
            $method->invoke($logger);
            $this->fail("reconnect doesn't throw Exception");
        } catch (\Exception $e) {
            $this->assertInstanceOf("\\Exception", $e);
        }
        $fp   = fopen("php://memory", "r");
        $prop = new \ReflectionProperty($logger, "socket");
        $prop->setAccessible(true);
        $prop->setValue($logger, $fp);
        $method->invoke($logger);
    }
}
