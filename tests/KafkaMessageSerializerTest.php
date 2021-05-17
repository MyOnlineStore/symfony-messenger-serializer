<?php
declare(strict_types=1);

namespace MyOnlineStore\Symfony\Messenger\Serializer\Tests;

use MyOnlineStore\Symfony\Messenger\Serializer\KafkaMessage;
use MyOnlineStore\Symfony\Messenger\Serializer\KafkaMessageSerializer;
use MyOnlineStore\Symfony\Messenger\Serializer\MessageName;
use MyOnlineStore\Symfony\Messenger\Serializer\MessageNameMapper;
use MyOnlineStore\Symfony\Messenger\Serializer\MessageNameMappingFailed;
use MyOnlineStore\Symfony\Messenger\Serializer\UnmappedMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class KafkaMessageSerializerTest extends TestCase
{
    /** @var DecoderInterface&MockObject */
    private $decoder;

    /** @var DenormalizerInterface&MockObject */
    private $denormalizer;

    /** @var EncoderInterface&MockObject */
    private $encoder;

    /** @var MessageNameMapper&MockObject */
    private $messageNameMapper;

    /** @var NormalizerInterface&MockObject */
    private $normalizer;

    /** @var KafkaMessageSerializer */
    private $serializer;

    protected function setUp(): void
    {
        $this->serializer = new KafkaMessageSerializer(
            $this->decoder = $this->createMock(DecoderInterface::class),
            $this->denormalizer = $this->createMock(DenormalizerInterface::class),
            $this->encoder = $this->createMock(EncoderInterface::class),
            $this->messageNameMapper = $this->createMock(MessageNameMapper::class),
            $this->normalizer = $this->createMock(NormalizerInterface::class)
        );
    }

    public function testDecode(): void
    {
        $this->messageNameMapper->expects(self::once())
            ->method('getMessageFromName')
            ->with(MessageName::fromString('foo.v1'))
            ->willReturn(\stdClass::class);

        $this->decoder->expects(self::once())
            ->method('decode')
            ->with('{"normalized-message"}', 'json')
            ->willReturn(['normalized-message']);

        $this->denormalizer->expects(self::once())
            ->method('denormalize')
            ->with(['normalized-message'], \stdClass::class, 'foo.v1')
            ->willReturn($message = $this->createMock(KafkaMessage::class));

        self::assertEquals(
            new Envelope($message),
            $this->serializer->decode(
                [
                    'key' => 'foo',
                    'headers' => [
                        'name' => 'foo.v1',
                    ],
                    'body' => '{"normalized-message"}',
                ]
            )
        );
    }

    public function testDecodeReturnsUnmappedMessage(): void
    {
        $messageName = MessageName::fromString('foo.v1');

        $this->messageNameMapper->expects(self::once())
            ->method('getMessageFromName')
            ->with($messageName)
            ->willThrowException(MessageNameMappingFailed::withMessageName($messageName));

        self::assertEquals(
            new Envelope(new UnmappedMessage('foo', ['name' => 'foo.v1'], '{"normalized-message"}')),
            $this->serializer->decode(
                [
                    'key' => 'foo',
                    'headers' => [
                        'name' => 'foo.v1',
                    ],
                    'body' => '{"normalized-message"}',
                ]
            )
        );
    }

    public function testEncode(): void
    {
        $message = $this->createMock(KafkaMessage::class);

        $message->expects(self::once())
            ->method('getKey')
            ->willReturn('foo');

        $this->messageNameMapper->expects(self::once())
            ->method('getNameFromMessage')
            ->with(\get_class($message))
            ->willReturn(MessageName::fromString('foo.v1'));

        $this->normalizer->expects(self::once())
            ->method('normalize')
            ->with($message, 'foo.v1')
            ->willReturn(['normalized-message']);

        $this->encoder->expects(self::once())
            ->method('encode')
            ->with(['normalized-message'], 'json')
            ->willReturn('{"normalized-message"}');

        self::assertEquals(
            [
                'key' => 'foo',
                'headers' => [
                    'name' => 'foo.v1',
                ],
                'body' => '{"normalized-message"}',
            ],
            $this->serializer->encode(new Envelope($message))
        );
    }
}