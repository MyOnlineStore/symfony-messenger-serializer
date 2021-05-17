<?php
declare(strict_types=1);

namespace MyOnlineStore\Symfony\Messenger\Serializer;

use Webmozart\Assert\Assert;

/**
 * @psalm-immutable
 */
final class MessageName
{
    /** @var string */
    private $name;

    /** @var string */
    private $version;

    private function __construct(string $name, string $version)
    {
        $this->name = $name;
        $this->version = $version;
    }

    /**
     * Construct a MessageName from separate name and version, eg `foo` and `v1`.
     *
     * @throws \InvalidArgumentException
     *
     * @psalm-pure
     */
    public static function fromNameAndVersion(string $name, string $version): self
    {
        Assert::notEmpty($name);
        Assert::notEmpty($version);

        return new self($name, $version);
    }

    /**
     * Construct a MessageName from a full name and version, eg `foo.v1`.
     *
     * @throws \InvalidArgumentException
     *
     * @psalm-pure
     */
    public static function fromString(string $messageName): self
    {
        Assert::contains($messageName, '.');

        [$name, $version] = \explode('.', $messageName);

        Assert::notEmpty($name);
        Assert::notEmpty($version);

        return new self($name, $version);
    }

    public function toString(): string
    {
        return \sprintf('%s.%s', $this->name, $this->version);
    }
}
