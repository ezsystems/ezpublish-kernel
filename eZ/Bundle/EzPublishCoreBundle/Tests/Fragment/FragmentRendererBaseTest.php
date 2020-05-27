<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Fragment;

use eZ\Publish\Core\MVC\Symfony\Component\Serializer\SerializerTrait;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;

abstract class FragmentRendererBaseTest extends TestCase
{
    use SerializerTrait;

    public function testRendererControllerReferenceWithCompoundMatcher(): ControllerReference
    {
        $reference = new ControllerReference('FooBundle:bar:baz');
        $compoundMatcher = new SiteAccess\Matcher\Compound\LogicalAnd([]);
        $subMatchers = [
            'Map\URI' => new SiteAccess\Matcher\Map\URI([]),
            'Map\Host' => new SiteAccess\Matcher\Map\Host([]),
        ];
        $compoundMatcher->setSubMatchers($subMatchers);
        $siteAccess = new SiteAccess(
            'test',
            'test',
            $compoundMatcher
        );

        $request = $this->getRequest($siteAccess);
        $options = ['foo' => 'bar'];
        $expectedReturn = '/_fragment?foo=bar';
        $this->innerRenderer
            ->expects($this->once())
            ->method('render')
            ->with($reference, $request, $options)
            ->will($this->returnValue($expectedReturn));

        $renderer = $this->getRenderer();
        $this->assertSame($expectedReturn, $renderer->render($reference, $request, $options));
        $this->assertArrayHasKey('serialized_siteaccess', $reference->attributes);
        $serializedSiteAccess = json_encode($siteAccess);
        $this->assertSame($serializedSiteAccess, $reference->attributes['serialized_siteaccess']);
        $this->assertArrayHasKey('serialized_siteaccess_matcher', $reference->attributes);
        $this->assertSame(
            $this->getSerializer()->serialize(
                $siteAccess->matcher,
                'json'
            ),
            $reference->attributes['serialized_siteaccess_matcher']
        );
        $this->assertArrayHasKey('serialized_siteaccess_sub_matchers', $reference->attributes);
        foreach ($siteAccess->matcher->getSubMatchers() as $subMatcher) {
            $this->assertSame(
                $this->getSerializer()->serialize(
                    $subMatcher,
                    'json'
                ),
                $reference->attributes['serialized_siteaccess_sub_matchers'][get_class($subMatcher)]
            );
        }

        return $reference;
    }

    abstract public function getRequest(SiteAccess $siteAccess): Request;

    abstract public function getRenderer(): FragmentRendererInterface;
}
