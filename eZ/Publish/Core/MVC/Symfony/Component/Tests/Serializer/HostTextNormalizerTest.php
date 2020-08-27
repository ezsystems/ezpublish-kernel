<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer;

use eZ\Publish\Core\MVC\Symfony\Component\Serializer\HostTextNormalizer;
use eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer\Stubs\SerializerStub;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\HostText;
use eZ\Publish\Core\Search\Tests\TestCase;

final class HostTextNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new HostTextNormalizer();
        $normalizer->setSerializer(new SerializerStub());

        $matcher = new HostText([
            'prefix' => 'foo',
            'suffix' => 'bar',
        ]);

        $this->assertEquals(
            [
                'siteAccessesConfiguration' => [
                    'prefix' => 'foo',
                    'suffix' => 'bar',
                ],
            ],
            $normalizer->normalize($matcher)
        );
    }

    public function testSupportsNormalization(): void
    {
        $normalizer = new HostTextNormalizer();

        $this->assertTrue($normalizer->supportsNormalization($this->createMock(HostText::class)));
        $this->assertFalse($normalizer->supportsNormalization($this->createMock(Matcher::class)));
    }
}
