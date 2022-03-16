<?php
declare(strict_types=1);

namespace MyOnlineStore\Symfony\Messenger\Serializer;

/**
 * @psalm-immutable
 */
final class UnmappedMessage
{
    public string $key;
    /** @var array<string, string> */
    public array $headers;
    public string $body;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(string $key, array $headers, string $body)
    {
        $this->key = $key;
        $this->headers = $headers;
        $this->body = $body;
    }
}
