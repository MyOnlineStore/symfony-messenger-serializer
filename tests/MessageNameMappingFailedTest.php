<?php
declare(strict_types=1);

namespace MyOnlineStore\Symfony\Messenger\Serializer\Tests;

use MyOnlineStore\Symfony\Messenger\Serializer\KafkaMessage;
use MyOnlineStore\Symfony\Messenger\Serializer\MessageName;
use MyOnlineStore\Symfony\Messenger\Serializer\MessageNameMappingFailed;
use PHPUnit\Framework\TestCase;

final class MessageNameMappingFailedTest extends TestCase
{
    public function testWithMessage(): void
    {
        $message = \get_class($this->createMock(KafkaMessage::class));
        $exception = MessageNameMappingFailed::withMessage($message);

        self::assertStringContainsString($message, $exception->getMessage());
    }

    public function testWithMessageName(): void
    {
        $messageName = MessageName::fromString('foo.v1');
        $exception = MessageNameMappingFailed::withMessageName($messageName);

        self::assertStringContainsString($messageName->toString(), $exception->getMessage());
    }
}
