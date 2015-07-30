<?php

/**
 * File containing the IndexRequestListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\IndexRequestListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class IndexRequestListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    /**
     * @var IndexRequestListener
     */
    private $indexRequestEventListener;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var GetResponseEvent
     */
    private $event;

    /**
     * @var HttpKernelInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpKernel;

    protected function setUp()
    {
        parent::setUp();

        $this->configResolver = $this->getMock('eZ\Publish\Core\MVC\ConfigResolverInterface');

        $this->indexRequestEventListener = new IndexRequestListener($this->configResolver);

        $this->request = $this
            ->getMockBuilder('Symfony\\Component\\HttpFoundation\\Request')
            ->setMethods(array('getSession', 'hasSession'))
            ->getMock();

        $this->httpKernel = $this->getMock('Symfony\\Component\\HttpKernel\\HttpKernelInterface');
        $this->event = new GetResponseEvent(
            $this->httpKernel,
            $this->request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }

    public function testSubscribedEvents()
    {
        $this->assertSame(
            array(
                KernelEvents::REQUEST => array(
                    array('onKernelRequestIndex', 40),
                ),
            ),
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
        return array(
            array('/', '/foo', '/foo'),
            array('/', '/foo/', '/foo/'),
            array('/', '/foo/bar', '/foo/bar'),
            array('/', 'foo/bar', '/foo/bar'),
            array('', 'foo/bar', '/foo/bar'),
            array('', '/foo/bar', '/foo/bar'),
            array('', '/foo/bar/', '/foo/bar/'),
        );
    }

    public function testOnKernelRequestIndexNotOnIndexPage()
    {
        $this->request->attributes->set('semanticPathinfo', '/anyContent');
        $this->indexRequestEventListener->onKernelRequestIndex($this->event);
        $this->assertFalse($this->request->attributes->has('needsForward'));
    }
}
