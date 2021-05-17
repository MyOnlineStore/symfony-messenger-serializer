<?php
declare(strict_types=1);

namespace MyOnlineStore\Symfony\Messenger\Serializer;

interface KafkaMessage
{
    /**
     * Get the key that will be put into the Kafka topic key. This key is used
     * for partitioning among other things.
     *
     * @psalm-pure
     */
    public function getKey(): string;
}
