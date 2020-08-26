<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer;

use eZ\Publish\Core\MVC\Symfony\Component\Serializer\CompoundMatcherNormalizer;
use eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer\Stubs\MatcherStub;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound;
use PHPUnit\Framework\TestCase;

final class CompoundMatcherNormalizerTest extends TestCase
{
    public function testNormalization(): void
    {
        $matcher = $this->createMock(Compound::class);
        $matcher->method('getSubMatchers')->willReturn([
            new MatcherStub('foo'),
            new MatcherStub('bar'),
            new MatcherStub('baz'),
        ]);

        $normalizer = new CompoundMatcherNormalizer();

        $this->assertEquals(
            [
                'subMatchers' => [
                    ['data' => 'foo'],
                    ['data' => 'bar'],
                    ['data' => 'baz'],
                ],
            ],
            $normalizer->normalize($matcher)
        );
    }

    public function testSupportsNormalization(): void
    {
        $normalizer = new CompoundMatcherNormalizer();

        $this->assertTrue($normalizer->supportsNormalization($this->createMock(Compound::class)));
        $this->assertFalse($normalizer->supportsNormalization($this->createMock(Matcher::class)));
    }
}
