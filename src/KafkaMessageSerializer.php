<?php
declare(strict_types=1);

namespace MyOnlineStore\Symfony\Messenger\Serializer;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class KafkaMessageSerializer implements SerializerInterface
{
    private DecoderInterface $decoder;
    private DenormalizerInterface $denormalizer;
    private EncoderInterface $encoder;
    private MessageNameMapper $messageNameMapper;
    private NormalizerInterface $normalizer;

    public function __construct(
        DecoderInterface $decoder,
        DenormalizerInterface $denormalizer,
        EncoderInterface $encoder,
        MessageNameMapper $messageNameMapper,
        NormalizerInterface $normalizer
    ) {
        $this->decoder = $decoder;
        $this->denormalizer = $denormalizer;
        $this->encoder = $encoder;
        $this->messageNameMapper = $messageNameMapper;
        $this->normalizer = $normalizer;
    }

    /**
     * Decodes a message into an object of the FQCN given by
     * MessageNameMapper::getMessageFromName(). If mapping fails, an
     * UnmappedMessage will be returned.
     *
     * @param array<array-key, mixed> $encodedEnvelope
     *
     * @throws ExceptionInterface
     */
    public function decode(array $encodedEnvelope): Envelope
    {
        \assert(\array_key_exists('key', $encodedEnvelope));
        \assert(\is_string($encodedEnvelope['key']));
        \assert(\array_key_exists('headers', $encodedEnvelope));
        \assert(\is_array($encodedEnvelope['headers']));
        \assert(\array_key_exists('body', $encodedEnvelope));
        \assert(\is_string($encodedEnvelope['body']));

        try {
            $messageName = MessageName::fromString((string) ($encodedEnvelope['headers']['name'] ?? ''));

            $message = $this->denormalizer->denormalize(
                $this->decoder->decode($encodedEnvelope['body'], 'json'),
                $this->messageNameMapper->getMessageFromName($messageName),
                $messageName->toString()
            );

            \assert(\is_object($message));
        } catch (\InvalidArgumentException | MessageNameMappingFailed $exception) {
            /** @psalm-suppress MixedArgumentTypeCoercion */
            $message = new UnmappedMessage(
                $encodedEnvelope['key'],
                $encodedEnvelope['headers'],
                $encodedEnvelope['body']
            );
        }

        return new Envelope($message);
    }

    /**
     * @return array{key: string, headers: array<string, string>, body: string}
     *
     * @throws ExceptionInterface
     * @throws \InvalidArgumentException
     * @throws MessageNameMappingFailed
     */
    public function encode(Envelope $envelope): array
    {
        $message = $envelope->getMessage();
        \assert($message instanceof KafkaMessage);

        $messageName = $this->messageNameMapper->getNameFromMessage(\get_class($message))->toString();

        return [
            'key' => $message->getKey(),
            'headers' => [
                'name' => $messageName,
            ],
            'body' => $this->encoder->encode(
                $this->normalizer->normalize($message, $messageName),
                'json',
                [
                    JsonEncode::OPTIONS =>  \JSON_UNESCAPED_SLASHES,
                ]
            ),
            'timestamp_ms' => $message instanceof TimestampAwareMessage ?
                $message->getTimestamp() :
                null,
        ];
    }
}
