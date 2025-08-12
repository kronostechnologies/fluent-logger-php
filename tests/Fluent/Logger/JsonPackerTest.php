<?php
namespace Fluent\Logger;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JsonPackerTest extends TestCase
{
    const TAG           = "debug.test";
    const EXPECTED_TIME = 123456789;

    protected $time;
    protected $expected_data = array();

    public function setUp(): void
    {
        $this->expected_data = array("abc" => "def");
    }

    public function testPack()
    {
        $entity = new Entity(self::TAG, $this->expected_data, self::EXPECTED_TIME);

        $packer = new JsonPacker();
        $result = $packer->pack($entity);

        /*
         * expected format.
         * ["<Tag>", <Unixtime>, {object}]
         */
        $this->assertStringMatchesFormat('["%s",%d,{"%s":"%s"}]', $result, "unexpected format returns");

        return json_decode($result, true);
    }

    #[Test]
    #[Depends('testPack')]
    public function testPackReturnTag($result)
    {
        $this->assertEquals($result['0'], self::TAG);
    }

    #[Test]
    #[Depends('testPack')]
    public function testPackReturnTime($result)
    {
        $this->assertEquals($result['1'], self::EXPECTED_TIME);
    }

    #[Test]
    #[Depends('testPack')]
    public function testPackReturnData($result)
    {
        $this->assertEquals($result['2'], $this->expected_data);
    }
}
