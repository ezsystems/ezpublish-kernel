<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer;

use eZ\Publish\Core\MVC\Symfony\Component\Serializer\URIElementNormalizer;
use eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer\Stubs\SerializerStub;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement;
use PHPUnit\Framework\TestCase;

final class URIElementNormalizerTest extends TestCase
{
    public function testNormalization()
    {
        $normalizer = new URIElementNormalizer();
        $normalizer->setSerializer(new SerializerStub());

        $matcher = new URIElement(2);
        // Set request and invoke match to initialize HostElement::$hostElements
        $matcher->setRequest(SimplifiedRequest::fromUrl('http://ezpublish.dev/foo/bar'));
        $matcher->match();

        $this->assertEquals(
            [
                'elementNumber' => 2,
                'uriElements' => ['foo', 'bar'],
            ],
            $normalizer->normalize($matcher)
        );
    }

    public function testSupportsNormalization()
    {
        $normalizer = new URIElementNormalizer();

        $this->assertTrue($normalizer->supportsNormalization($this->createMock(URIElement::class)));
        $this->assertFalse($normalizer->supportsNormalization($this->createMock(Matcher::class)));
    }
}
