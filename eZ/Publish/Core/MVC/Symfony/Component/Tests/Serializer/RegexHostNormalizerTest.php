<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer;

use eZ\Publish\Core\MVC\Symfony\Component\Serializer\RegexHostNormalizer;
use eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer\Stubs\SerializerStub;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Regex\Host;
use eZ\Publish\Core\Search\Tests\TestCase;

final class RegexHostNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new RegexHostNormalizer();
        $normalizer->setSerializer(new SerializerStub());

        $matcher = new Host([
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

    public function testSupportsNormalization(): void
    {
        $normalizer = new RegexHostNormalizer();

        $this->assertTrue($normalizer->supportsNormalization($this->createMock(Host::class)));
        $this->assertFalse($normalizer->supportsNormalization($this->createMock(Matcher::class)));
    }
}
