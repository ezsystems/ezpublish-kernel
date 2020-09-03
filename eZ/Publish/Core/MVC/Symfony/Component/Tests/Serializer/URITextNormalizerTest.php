<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer;

use eZ\Publish\Core\MVC\Symfony\Component\Serializer\URITextNormalizer;
use eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer\Stubs\SerializerStub;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIText;
use eZ\Publish\Core\Search\Tests\TestCase;

final class URITextNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new URITextNormalizer();
        $normalizer->setSerializer(new SerializerStub());

        $matcher = new URIText([
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
        $normalizer = new URITextNormalizer();

        $this->assertTrue($normalizer->supportsNormalization($this->createMock(URIText::class)));
        $this->assertFalse($normalizer->supportsNormalization($this->createMock(Matcher::class)));
    }
}
