<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Fragment;

use eZ\Bundle\EzPublishCoreBundle\Fragment\InlineFragmentRenderer;
use eZ\Publish\Core\MVC\Symfony\Component\Serializer\SerializerTrait;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class InlineFragmentRendererTest extends DecoratedFragmentRendererTest
{
    use SerializerTrait;

    public function testRendererControllerReference()
    {
        $reference = new ControllerReference('FooBundle:bar:baz');
        $matcher = new SiteAccess\Matcher\HostElement(1);
        $siteAccess = new SiteAccess(
            'test',
            'test',
            $matcher
        );
        $request = new Request();
        $request->attributes->set('siteaccess', $siteAccess);
        $request->attributes->set('semanticPathinfo', '/foo/bar');
        $request->attributes->set('viewParametersString', '/(foo)/bar');
        $options = ['foo' => 'bar'];
        $expectedReturn = '/_fragment?foo=bar';
        $this->innerRenderer
            ->expects($this->once())
            ->method('render')
            ->with($reference, $request, $options)
            ->will($this->returnValue($expectedReturn));

        $renderer = new InlineFragmentRenderer($this->innerRenderer);
        $this->assertSame($expectedReturn, $renderer->render($reference, $request, $options));
        $this->assertTrue(isset($reference->attributes['serialized_siteaccess']));
        $serializedSiteAccess = json_encode($siteAccess);
        $this->assertSame($serializedSiteAccess, $reference->attributes['serialized_siteaccess']);
        $this->assertTrue(isset($reference->attributes['serialized_siteaccess_matcher']));
        $this->assertSame(
            $this->getSerializer()->serialize(
                $siteAccess->matcher,
                'json'
            ),
            $reference->attributes['serialized_siteaccess_matcher']
        );
        $this->assertTrue(isset($reference->attributes['semanticPathinfo']));
        $this->assertSame('/foo/bar', $reference->attributes['semanticPathinfo']);
        $this->assertTrue(isset($reference->attributes['viewParametersString']));
        $this->assertSame('/(foo)/bar', $reference->attributes['viewParametersString']);
    }

    public function testRendererControllerReferenceWithCompoundMatcher()
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
        $request = new Request();
        $request->attributes->set('siteaccess', $siteAccess);
        $request->attributes->set('semanticPathinfo', '/foo/bar');
        $request->attributes->set('viewParametersString', '/(foo)/bar');
        $options = ['foo' => 'bar'];
        $expectedReturn = '/_fragment?foo=bar';
        $this->innerRenderer
            ->expects($this->once())
            ->method('render')
            ->with($reference, $request, $options)
            ->will($this->returnValue($expectedReturn));

        $renderer = new InlineFragmentRenderer($this->innerRenderer);
        $this->assertSame($expectedReturn, $renderer->render($reference, $request, $options));
        $this->assertTrue(isset($reference->attributes['serialized_siteaccess']));
        $serializedSiteAccess = json_encode($siteAccess);
        $this->assertSame($serializedSiteAccess, $reference->attributes['serialized_siteaccess']);
        $this->assertTrue(isset($reference->attributes['serialized_siteaccess_matcher']));
        $this->assertSame(
            $this->getSerializer()->serialize(
                $siteAccess->matcher,
                'json'
            ),
            $reference->attributes['serialized_siteaccess_matcher']
        );
        $this->assertTrue(isset($reference->attributes['serialized_siteaccess_sub_matchers']));
        foreach ($siteAccess->matcher->getSubMatchers() as $subMatcher) {
            $this->assertSame(
                $this->getSerializer()->serialize(
                    $subMatcher,
                    'json'
                ),
                $reference->attributes['serialized_siteaccess_sub_matchers'][get_class($subMatcher)]
            );
        }
        $this->assertTrue(isset($reference->attributes['semanticPathinfo']));
        $this->assertSame('/foo/bar', $reference->attributes['semanticPathinfo']);
        $this->assertTrue(isset($reference->attributes['viewParametersString']));
        $this->assertSame('/(foo)/bar', $reference->attributes['viewParametersString']);
    }
}
