<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer;

use eZ\Publish\Core\MVC\Symfony\Component\Serializer\SimplifiedRequestNormalizer;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use PHPUnit\Framework\TestCase;
use stdClass;

final class SimplifiedRequestNormalizerTest extends TestCase
{
    public function testNormalize()
    {
        $request = new SimplifiedRequest([
            'scheme' => 'http',
            'host' => 'www.example.com',
            'port' => 8080,
            'pathinfo' => '/foo',
            'queryParams' => ['param' => 'value', 'this' => 'that'],
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Accept-Language' => 'pl-PL,pl;q=0.9,en-US;q=0.8,en;q=0.7',
                'User-Agent' => 'Mozilla/5.0',
                'Cookie' => 'eZSESSID21232f297a57a5a743894a0e4a801fc3=mgbs2p6lv936hb5hmdd2cvq6bq',
                'Connection' => 'keep-alive',
            ],
            'languages' => ['pl-PL', 'en-US'],
        ]);

        $normalizer = new SimplifiedRequestNormalizer();

        $this->assertEquals([
            'scheme' => 'http',
            'host' => 'www.example.com',
            'port' => 8080,
            'pathinfo' => '/foo',
            'queryParams' => ['param' => 'value', 'this' => 'that'],
            'headers' => [],
            'languages' => ['pl-PL', 'en-US'],
        ], $normalizer->normalize($request));
    }

    public function testSupportsNormalization()
    {
        $normalizer = new SimplifiedRequestNormalizer();

        $this->assertTrue($normalizer->supportsNormalization(new SimplifiedRequest()));
        $this->assertFalse($normalizer->supportsNormalization(new stdClass()));
    }
}
