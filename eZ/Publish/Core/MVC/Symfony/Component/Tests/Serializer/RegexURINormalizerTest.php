<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer;

use eZ\Publish\Core\MVC\Symfony\Component\Serializer\RegexURINormalizer;
use eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer\Stubs\SerializerStub;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Regex\URI;
use eZ\Publish\Core\Search\Tests\TestCase;

final class RegexURINormalizerTest extends TestCase
{
    public function testNormalize()
    {
        $normalizer = new RegexURINormalizer();
        $normalizer->setSerializer(new SerializerStub());

        $matcher = new URI([
            'regex' => '/^Foo(.*)/(.*)/',
            'itemNumber' => 2,
        ]);

        $this->assertEquals(
            [
                'siteAccessesConfiguration' => [
                    'regex' => '/^Foo(.*)/(.*)/',
                    'itemNumber' => 2,
                ],
            ],
            $normalizer->normalize($matcher)
        );
    }

    public function testSupportsNormalization()
    {
        $normalizer = new RegexURINormalizer();

        $this->assertTrue($normalizer->supportsNormalization($this->createMock(URI::class)));
        $this->assertFalse($normalizer->supportsNormalization($this->createMock(Matcher::class)));
    }
}
