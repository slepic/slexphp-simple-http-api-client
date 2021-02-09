<?php

declare(strict_types=1);

namespace BodySerialization\TypeMap\Serializer;

use PHPUnit\Framework\TestCase;
use Slexphp\Http\BodySerialization\Contracts\Serializer\BodySerializationExceptionInterface;
use Slexphp\Http\BodySerialization\Contracts\Serializer\BodySerializerInterface;
use Slexphp\Http\BodySerialization\TypeMap\Serializer\BodySerializer;
use Slexphp\Serialization\Contracts\Encoder\EncoderInterface;

class BodySerializerTest extends TestCase
{
    public function testImplements(): void
    {
        $deserializer = new BodySerializer([]);
        self::assertInstanceOf(BodySerializerInterface::class, $deserializer);
    }

    public function testExactMatchSuccess(): void
    {
        $contentType = ':content-type:';
        $rawBody = ':raw-body:';
        $parsedBody = ['value'];
        $decoder1 = self::createMock(EncoderInterface::class);
        $decoder1->expects(self::never())
            ->method('encode');
        $decoder2 = self::createMock(EncoderInterface::class);
        $decoder2->expects(self::once())
            ->method('encode')
            ->with($parsedBody)
            ->willReturn($rawBody);
        $deserializer = new BodySerializer([':unknown:' => $decoder1, $contentType => $decoder2]);
        $output = $deserializer->serializeBody($contentType, $parsedBody);
        self::assertSame($rawBody, $output);
    }

    public function testSubstringMatchSuccess(): void
    {
        $contentType = ':content-type:';
        $rawBody = ':raw-body:';
        $parsedBody = ['value'];
        $decoder1 = self::createMock(EncoderInterface::class);
        $decoder1->expects(self::never())
            ->method('encode');
        $decoder2 = self::createMock(EncoderInterface::class);
        $decoder2->expects(self::once())
            ->method('encode')
            ->with($parsedBody)
            ->willReturn($rawBody);
        $deserializer = new BodySerializer([':unknown:' => $decoder1, $contentType => $decoder2]);
        $output = $deserializer->serializeBody($contentType . '; charset=utf-8', $parsedBody);
        self::assertSame($rawBody, $output);
    }

    public function testNoMatchException(): void
    {
        $contentType = ':content-type:';
        $parsedBody = ['value'];
        $decoder = self::createMock(EncoderInterface::class);
        $decoder->expects(self::never())
            ->method('encode');
        $deserializer = new BodySerializer([':unknown:' => $decoder]);
        self::expectException(BodySerializationExceptionInterface::class);
        $deserializer->serializeBody($contentType, $parsedBody);
    }

    public function testCorrectExceptionIsThrownIfDecoderThrows(): void
    {
        $contentType = ':content-type:';
        $parsedBody = ['value'];
        $decoder = self::createMock(EncoderInterface::class);
        $decoder->expects(self::once())
            ->method('encode')
            ->with($parsedBody)
            ->willThrowException(new \Exception('decoder exception'));
        $deserializer = new BodySerializer([$contentType => $decoder]);
        self::expectException(BodySerializationExceptionInterface::class);
        $deserializer->serializeBody($contentType, $parsedBody);
    }
}
