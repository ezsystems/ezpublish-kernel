<?php

namespace eZ\Publish\Core\FieldType\Tests\RichText;

use eZ\Publish\Core\FieldType\RichText\XSLTProcessorFunctions;
use PHPUnit\Framework\TestCase;

class XSLTProcessorFunctionsTest extends TestCase
{
    /**
     * @dataProvider providerForIsValidUrl
     */
    public function testIsValidUrl($url, $expected)
    {
        $this->assertEquals($expected, XSLTProcessorFunctions::isValidUrl($url));
    }

    public function providerForIsValidUrl()
    {
        return [
            ['http://example.url', true],
            ['https://example.url', true],
            ['http://192.168.56.101/segment#/fragment%20url', true],
            ['http://example.url/segment', true],
            ['http://user:password@example.url:8001/', true],
            ['http://example.url?with[square]=brackets', true],
            ['http://example.url?with=value%20with%20spaces', true],
            ['http://example.url#fragment', true],
            ['http://example.url.with.#fragment', true],
            ['ftp://example.com/pub/file.txt', true],
            ['ftp://user:password@example.com/pub/file.txt', true],
            ['ftps://example.com/pub/file.txt', true],
            ['ftps://user:password@example.com/pub/file.txt', true],
            ['#fragment', true],
            ['ezlocation://72', true],
            ['ezlocation://72#fragment', true],
            ['ezcontent://70', true],
            ['ezcontent://72#fragment', true],
            ["javascript:alert('BOOM!')", false],
            ["vbscript:alert('BOOM!')", false],
            ['Simple text', false],
            ['script&gt;', false],
            ["<script>alert('boom');</script>", false],
        ];
    }
}
