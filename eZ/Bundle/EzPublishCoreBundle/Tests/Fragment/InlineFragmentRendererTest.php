<?php

/**
 * File containing the InlineFragmentRendererTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Fragment;

use eZ\Bundle\EzPublishCoreBundle\Fragment\InlineFragmentRenderer;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class InlineFragmentRendererTest extends DecoratedFragmentRendererTest
{
    public function testRendererControllerReference()
    {
        $reference = new ControllerReference('FooBundle:bar:baz');
        $siteAccess = new SiteAccess('test', 'test');
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
        $this->assertSame(serialize($siteAccess), $reference->attributes['serialized_siteaccess']);
        $this->assertTrue(isset($reference->attributes['semanticPathinfo']));
        $this->assertSame('/foo/bar', $reference->attributes['semanticPathinfo']);
        $this->assertTrue(isset($reference->attributes['viewParametersString']));
        $this->assertSame('/(foo)/bar', $reference->attributes['viewParametersString']);
    }
}
