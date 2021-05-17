<?php
declare(strict_types=1);

namespace MyOnlineStore\Symfony\Messenger\Serializer\Tests;

use MyOnlineStore\Symfony\Messenger\Serializer\UnmappedMessage;
use PHPUnit\Framework\TestCase;

final class UnmappedMessageTest extends TestCase
{
    public function testAccessors(): void
    {
        $message = new UnmappedMessage('foo', ['foo' => 'bar'], 'bar');

        self::assertSame('foo', $message->key);
        self::assertSame(['foo' => 'bar'], $message->headers);
        self::assertSame('bar', $message->body);
    }
}
