<?php

declare(strict_types=1);

namespace Slexphp\Tests\Http\BodySerialization\TypeMap\Deserializer;

use PHPUnit\Framework\TestCase;
use Slexphp\Http\BodySerialization\Contracts\Deserializer\BodyDeserializationExceptionInterface;
use Slexphp\Http\BodySerialization\Contracts\Deserializer\BodyDeserializerInterface;
use Slexphp\Http\BodySerialization\TypeMap\Deserializer\BodyDeserializer;
use Slexphp\Serialization\Contracts\Decoder\DecoderInterface;

class BodyDeserializerTest extends TestCase
{
    public function testImplements(): void
    {
        $deserializer = new BodyDeserializer([]);
        self::assertInstanceOf(BodyDeserializerInterface::class, $deserializer);
    }

    public function testExactMatchSuccess(): void
    {
        $contentType = ':content-type:';
        $rawBody = ':raw-body:';
        $parsedBody = ['value'];
        $decoder1 = self::createMock(DecoderInterface::class);
        $decoder1->expects(self::never())
            ->method('decode');
        $decoder2 = self::createMock(DecoderInterface::class);
        $decoder2->expects(self::once())
            ->method('decode')
            ->with($rawBody)
            ->willReturn($parsedBody);
        $deserializer = new BodyDeserializer([':unknown:' => $decoder1, $contentType => $decoder2]);
        $output = $deserializer->deserializeBody($contentType, $rawBody);
        self::assertSame($parsedBody, $output);
    }

    public function testSubstringMatchSuccess(): void
    {
        $contentType = ':content-type:';
        $rawBody = ':raw-body:';
        $parsedBody = ['value'];
        $decoder1 = self::createMock(DecoderInterface::class);
        $decoder1->expects(self::never())
            ->method('decode');
        $decoder2 = self::createMock(DecoderInterface::class);
        $decoder2->expects(self::once())
            ->method('decode')
            ->with($rawBody)
            ->willReturn($parsedBody);
        $deserializer = new BodyDeserializer([':unknown:' => $decoder1, $contentType => $decoder2]);
        $output = $deserializer->deserializeBody($contentType . '; charset=utf-8', $rawBody);
        self::assertSame($parsedBody, $output);
    }

    public function testNoMatchException(): void
    {
        $contentType = ':content-type:';
        $rawBody = ':raw-body:';
        $decoder = self::createMock(DecoderInterface::class);
        $decoder->expects(self::never())
            ->method('decode');
        $deserializer = new BodyDeserializer([':unknown:' => $decoder]);
        self::expectException(BodyDeserializationExceptionInterface::class);
        $deserializer->deserializeBody($contentType, $rawBody);
    }

    public function testCorrectExceptionIsThrownIfDecoderThrows(): void
    {
        $contentType = ':content-type:';
        $rawBody = ':raw-body:';
        $decoder = self::createMock(DecoderInterface::class);
        $decoder->expects(self::once())
            ->method('decode')
            ->with($rawBody)
            ->willThrowException(new \Exception('decoder exception'));
        $deserializer = new BodyDeserializer([$contentType => $decoder]);
        self::expectException(BodyDeserializationExceptionInterface::class);
        $deserializer->deserializeBody($contentType, $rawBody);
    }
}
