<?php
declare(strict_types=1);

namespace MyOnlineStore\Symfony\Messenger\Serializer;

interface MessageNameMapper
{
    /**
     * Get the FQCN that represents the given MessageName. If given MessageName
     * was not found, a MessageNameMappingFailed must be thrown.
     *
     * @return class-string
     *
     * @throws MessageNameMappingFailed
     *
     * @psalm-pure
     */
    public function getMessageFromName(MessageName $messageName): string;

    /**
     * Get the MessageName for the given message FQCN. If given FQCN was not
     * found, a MessageNameMappingFailed must be thrown.
     *
     * @param class-string<KafkaMessage> $className
     *
     * @throws MessageNameMappingFailed
     *
     * @psalm-pure
     */
    public function getNameFromMessage(string $className): MessageName;
}
