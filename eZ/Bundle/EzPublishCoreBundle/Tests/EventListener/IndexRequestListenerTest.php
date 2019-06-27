<?php

/**
 * File containing the IndexRequestListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\IndexRequestListener;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use PHPUnit\Framework\TestCase;

class IndexRequestListenerTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $configResolver;

    /** @var IndexRequestListener */
    private $indexRequestEventListener;

    /** @var Request */
    private $request;

    /** @var GetResponseEvent */
    private $event;

    /** @var HttpKernelInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $httpKernel;

    protected function setUp()
    {
        parent::setUp();

        $this->configResolver = $this->createMock(ConfigResolverInterface::class);

        $this->indexRequestEventListener = new IndexRequestListener($this->configResolver);

        $this->request = $this
            ->getMockBuilder(Request::class)
            ->setMethods(['getSession', 'hasSession'])
            ->getMock();

        $this->httpKernel = $this->createMock(HttpKernelInterface::class);
        $this->event = new GetResponseEvent(
            $this->httpKernel,
            $this->request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }

    public function testSubscribedEvents()
    {
        $this->assertSame(
            [
                KernelEvents::REQUEST => [
                    ['onKernelRequestIndex', 40],
                ],
            ],
            $this->indexRequestEventListener->getSubscribedEvents()
        );
    }

    /**
     * @dataProvider indexPageProvider
     */
    public function testOnKernelRequestIndexOnIndexPage($requestPath, $configuredIndexPath, $expectedIndexPath)
    {
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('index_page')
            ->will($this->returnValue($configuredIndexPath));
        $this->request->attributes->set('semanticPathinfo', $requestPath);
        $this->indexRequestEventListener->onKernelRequestIndex($this->event);
        $this->assertEquals($expectedIndexPath, $this->request->attributes->get('semanticPathinfo'));
        $this->assertTrue($this->request->attributes->get('needsForward'));
    }

    public function indexPageProvider()
    {
        return [
            ['/', '/foo', '/foo'],
            ['/', '/foo/', '/foo/'],
            ['/', '/foo/bar', '/foo/bar'],
            ['/', 'foo/bar', '/foo/bar'],
            ['', 'foo/bar', '/foo/bar'],
            ['', '/foo/bar', '/foo/bar'],
            ['', '/foo/bar/', '/foo/bar/'],
        ];
    }

    public function testOnKernelRequestIndexNotOnIndexPage()
    {
        $this->request->attributes->set('semanticPathinfo', '/anyContent');
        $this->indexRequestEventListener->onKernelRequestIndex($this->event);
        $this->assertFalse($this->request->attributes->has('needsForward'));
    }
}
