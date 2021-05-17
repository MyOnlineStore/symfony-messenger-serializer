<?php
declare(strict_types=1);

namespace MyOnlineStore\Symfony\Messenger\Serializer;

final class MessageNameMappingFailed extends \Exception
{
    /**
     * @param class-string<KafkaMessage> $message
     */
    public static function withMessage(string $message): self
    {
        return new self(\sprintf('No name found for message "%s".', $message));
    }

    public static function withMessageName(MessageName $messageName): self
    {
        return new self(\sprintf('No message found for name "%s".', $messageName->toString()));
    }
}
