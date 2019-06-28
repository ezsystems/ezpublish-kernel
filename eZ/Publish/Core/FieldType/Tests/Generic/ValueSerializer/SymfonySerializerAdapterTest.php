<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Tests\Generic\ValueSerializer;

use eZ\Publish\Core\FieldType\Generic\Value;
use eZ\Publish\Core\FieldType\Generic\ValueSerializer\SymfonySerializerAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SymfonySerializerAdapterTest extends TestCase
{
    private const TEST_FORMAT = 'csv';
    private const TEST_CONTEXT = ['foo' => 'bar'];

    /** @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $normalizer;

    /** @var \Symfony\Component\Serializer\Normalizer\DenormalizerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $denomalizer;

    /** @var \Symfony\Component\Serializer\Encoder\EncoderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $encoder;

    /** @var \Symfony\Component\Serializer\Encoder\DecoderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $decoder;

    /** @var \eZ\Publish\Core\FieldType\Generic\ValueSerializer\SymfonySerializerAdapter */
    private $adapter;

    protected function setUp(): void
    {
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->denomalizer = $this->createMock(DenormalizerInterface::class);
        $this->encoder = $this->createMock(EncoderInterface::class);
        $this->decoder = $this->createMock(DecoderInterface::class);

        $this->adapter = new SymfonySerializerAdapter(
            $this->normalizer,
            $this->denomalizer,
            $this->encoder,
            $this->decoder,
            self::TEST_FORMAT
        );
    }

    public function testNormalize(): void
    {
        $value = $this->createMock(Value::class);
        $data = ['value' => 'test'];

        $this->normalizer
            ->expects($this->once())
            ->method('normalize')
            ->with($value, self::TEST_FORMAT, self::TEST_CONTEXT)
            ->willReturn($data);

        $this->assertEquals($data, $this->adapter->normalize($value, self::TEST_CONTEXT));
    }

    public function testDenormalize(): void
    {
        $data = ['value' => 'test'];
        $value = $this->createMock(Value::class);

        $this->denomalizer
            ->expects($this->once())
            ->method('denormalize')
            ->with($data, Value::class, self::TEST_FORMAT, self::TEST_CONTEXT)
            ->willReturn($value);

        $this->assertEquals($value, $this->adapter->denormalize($data, Value::class, self::TEST_CONTEXT));
    }

    public function testEncode(): void
    {
        $data = ['value' => 'test'];
        $json = '{"value": "test"}';

        $this->encoder
            ->expects($this->once())
            ->method('encode')
            ->with($data, self::TEST_FORMAT, self::TEST_CONTEXT)
            ->willReturn($json);

        $this->assertEquals($json, $this->adapter->encode($data, self::TEST_CONTEXT));
    }

    public function testDecode(): void
    {
        $data = ['value' => 'test'];
        $json = '{"value": "test"}';

        $this->decoder
            ->expects($this->once())
            ->method('decode')
            ->with($json, self::TEST_FORMAT, self::TEST_CONTEXT)
            ->willReturn($data);

        $this->assertEquals($data, $this->adapter->decode($json, self::TEST_CONTEXT));
    }
}
