<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Fragment;

use eZ\Bundle\EzPublishCoreBundle\Fragment\DecoratedFragmentRenderer;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;
use Symfony\Component\HttpKernel\Fragment\RoutableFragmentRenderer;

class DecoratedFragmentRendererTest extends FragmentRendererBaseTest
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $innerRenderer;

    protected function setUp()
    {
        parent::setUp();
        $this->innerRenderer = $this->createMock(FragmentRendererInterface::class);
    }

    public function testSetFragmentPathNotRoutableRenderer()
    {
        $matcher = $this->createMock(SiteAccess\URILexer::class);
        $siteAccess = new SiteAccess('test', 'test', $matcher);
        $matcher
            ->expects($this->never())
            ->method('analyseLink');

        $renderer = new DecoratedFragmentRenderer($this->innerRenderer);
        $renderer->setSiteAccess($siteAccess);
        $renderer->setFragmentPath('foo');
    }

    public function testSetFragmentPath()
    {
        $matcher = $this->createMock(SiteAccess\URILexer::class);
        $siteAccess = new SiteAccess('test', 'test', $matcher);
        $matcher
            ->expects($this->once())
            ->method('analyseLink')
            ->with('/foo')
            ->will($this->returnValue('/bar/foo'));

        $innerRenderer = $this->createMock(RoutableFragmentRenderer::class);
        $innerRenderer
            ->expects($this->once())
            ->method('setFragmentPath')
            ->with('/bar/foo');
        $renderer = new DecoratedFragmentRenderer($innerRenderer);
        $renderer->setSiteAccess($siteAccess);
        $renderer->setFragmentPath('/foo');
    }

    public function testGetName()
    {
        $name = 'test';
        $this->innerRenderer
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($name));

        $renderer = new DecoratedFragmentRenderer($this->innerRenderer);
        $this->assertSame($name, $renderer->getName());
    }

    public function testRendererAbsoluteUrl()
    {
        $url = 'http://phoenix-rises.fm/foo/bar';
        $request = new Request();
        $options = ['foo' => 'bar'];
        $expectedReturn = '/_fragment?foo=bar';
        $this->innerRenderer
            ->expects($this->once())
            ->method('render')
            ->with($url, $request, $options)
            ->will($this->returnValue($expectedReturn));

        $renderer = new DecoratedFragmentRenderer($this->innerRenderer);
        $this->assertSame($expectedReturn, $renderer->render($url, $request, $options));
    }

    public function testRendererControllerReference()
    {
        $reference = new ControllerReference('FooBundle:bar:baz');
        $matcher = new SiteAccess\Matcher\URIElement(1);
        $siteAccess = new SiteAccess(
            'test',
            'test',
            $matcher
        );
        $request = new Request();
        $request->attributes->set('siteaccess', $siteAccess);
        $options = ['foo' => 'bar'];
        $expectedReturn = '/_fragment?foo=bar';
        $this->innerRenderer
            ->expects($this->once())
            ->method('render')
            ->with($reference, $request, $options)
            ->will($this->returnValue($expectedReturn));

        $renderer = new DecoratedFragmentRenderer($this->innerRenderer);
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
    }

    public function getRequest(SiteAccess $siteAccess): Request
    {
        $request = new Request();
        $request->attributes->set('siteaccess', $siteAccess);

        return $request;
    }

    public function getRenderer(): FragmentRendererInterface
    {
        return new DecoratedFragmentRenderer($this->innerRenderer);
    }
}
