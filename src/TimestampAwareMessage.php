<?php
declare(strict_types=1);

namespace MyOnlineStore\Symfony\Messenger\Serializer;

interface TimestampAwareMessage extends KafkaMessage
{
    /**
     * @return positive-int Timestamp in milliseconds
     */
    public function getTimestamp(): int;
}
