<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\Core\MVC\Symfony\Component\Serializer\RegexNormalizer;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Regex as RegexMatcher;
use PHPUnit\Framework\TestCase;

final class RegexNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new RegexNormalizer();

        $matcher = new class('/^Foo(.*)/(.*)/', 2) extends RegexMatcher {
            public function getName()
            {
                throw new NotImplementedException(__METHOD__);
            }
        };

        $this->assertEquals(
            [
                'regex' => '/^Foo(.*)/(.*)/',
                'itemNumber' => 2,
                'matchedSiteAccess' => null,
            ],
            $normalizer->normalize($matcher)
        );
    }

    public function testSupportsNormalization(): void
    {
        $normalizer = new RegexNormalizer();

        $this->assertTrue($normalizer->supportsNormalization($this->createMock(RegexMatcher::class)));
        $this->assertFalse($normalizer->supportsNormalization($this->createMock(Matcher::class)));
    }
}
