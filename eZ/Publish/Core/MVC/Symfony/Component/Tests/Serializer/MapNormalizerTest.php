<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer;

use eZ\Publish\Core\MVC\Symfony\Component\Serializer\MapNormalizer;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map as MapMatcher;
use PHPUnit\Framework\TestCase;

final class MapNormalizerTest extends TestCase
{
    public function testNormalization(): void
    {
        $normalizer = new MapNormalizer();

        $matcher = $this->createMock(MapMatcher::class);
        $matcher->method('getMapKey')->willReturn('foo');

        $this->assertEquals(
            [
                'key' => 'foo',
                'map' => [],
                'reverseMap' => [],
            ],
            $normalizer->normalize($matcher)
        );
    }

    public function testSupportsNormalization(): void
    {
        $normalizer = new MapNormalizer();

        $this->assertTrue($normalizer->supportsNormalization($this->createMock(MapMatcher::class)));
        $this->assertFalse($normalizer->supportsNormalization($this->createMock(Matcher::class)));
    }
}
