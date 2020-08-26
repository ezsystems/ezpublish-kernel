<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer;

use eZ\Publish\Core\MVC\Symfony\Component\Serializer\HostElementNormalizer;
use eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer\Stubs\SerializerStub;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\HostElement;
use PHPUnit\Framework\TestCase;

final class HostElementNormalizerTest extends TestCase
{
    public function testNormalization(): void
    {
        $normalizer = new HostElementNormalizer();
        $normalizer->setSerializer(new SerializerStub());

        $matcher = new HostElement(2);
        // Set request and invoke match to initialize HostElement::$hostElements
        $matcher->setRequest(SimplifiedRequest::fromUrl('http://ezpublish.dev/foo/bar'));
        $matcher->match();

        $this->assertEquals(
            [
                'elementNumber' => 2,
                'hostElements' => [
                    'ezpublish',
                    'dev',
                ],
            ],
            $normalizer->normalize($matcher)
        );
    }

    public function testSupportsNormalization(): void
    {
        $normalizer = new HostElementNormalizer();

        $this->assertTrue($normalizer->supportsNormalization($this->createMock(HostElement::class)));
        $this->assertFalse($normalizer->supportsNormalization($this->createMock(Matcher::class)));
    }
}
