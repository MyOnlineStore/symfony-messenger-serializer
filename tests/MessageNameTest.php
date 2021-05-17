<?php
declare(strict_types=1);

namespace MyOnlineStore\Symfony\Messenger\Serializer\Tests;

use MyOnlineStore\Symfony\Messenger\Serializer\MessageName;
use PHPUnit\Framework\TestCase;

final class MessageNameTest extends TestCase
{
    public function testAccessors(): void
    {
        self::assertSame('foo.v1', MessageName::fromString('foo.v1')->toString());
        self::assertSame('foo.v1', MessageName::fromNameAndVersion('foo', 'v1')->toString());
        self::assertEquals(MessageName::fromString('foo.v1'), MessageName::fromNameAndVersion('foo', 'v1'));
    }

    public function invalidStringProvider(): \Generator
    {
        yield ['foo'];
        yield ['foo.'];
        yield ['.foo'];
    }

    /**
     * @dataProvider invalidStringProvider
     */
    public function testDoesNotAllowInvalidNameFromString(string $messageName): void
    {
        $this->expectException(\InvalidArgumentException::class);

        MessageName::fromString($messageName);
    }

    public function invalidNameOrVersionProvider(): \Generator
    {
        yield ['', ''];
        yield ['foo', ''];
        yield ['', 'foo'];
    }

    /**
     * @dataProvider invalidNameOrVersionProvider
     */
    public function testDoesNotAllowInvalidNameOrVersion(string $name, string $version): void
    {
        $this->expectException(\InvalidArgumentException::class);

        MessageName::fromNameAndVersion($name, $version);
    }
}
