<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer;

use eZ\Publish\Core\MVC\Symfony\Component\Serializer\RegexNormalizer;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Regex as RegexMatcher;
use eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer\Stubs\RegexMatcher as RegexMatcherStub;
use PHPUnit\Framework\TestCase;

final class RegexNormalizerTest extends TestCase
{
    public function testNormalize()
    {
        $normalizer = new RegexNormalizer();
        $matcher = new RegexMatcherStub('/^Foo(.*)/(.*)/', 2);

        $this->assertEquals(
            [
                'regex' => '/^Foo(.*)/(.*)/',
                'itemNumber' => 2,
                'matchedSiteAccess' => null,
            ],
            $normalizer->normalize($matcher)
        );
    }

    public function testSupportsNormalization()
    {
        $normalizer = new RegexNormalizer();

        $this->assertTrue($normalizer->supportsNormalization($this->createMock(RegexMatcher::class)));
        $this->assertFalse($normalizer->supportsNormalization($this->createMock(Matcher::class)));
    }
}
