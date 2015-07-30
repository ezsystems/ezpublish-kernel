<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishDebugBundle\Tests\Collector;

use eZ\Bundle\EzPublishDebugBundle\Collector\EzPublishCoreCollector;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class EzPublishCoreCollectorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EzPublishCoreCollector
     */
    private $mainCollector;

    protected function setUp()
    {
        parent::setUp();
        $this->mainCollector = new EzPublishCoreCollector();
    }

    public function testAddGetCollector()
    {
        $collector = $this->getMock('\Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface');
        $name = 'foobar';
        $collector
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($name));

        $this->mainCollector->addCollector($collector);
        $this->assertSame($collector, $this->mainCollector->getCollector($name));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetInvalidCollector()
    {
        $collector = $this->getMock('\Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface');
        $this->mainCollector->addCollector($collector);
        $this->assertSame($collector, $this->mainCollector->getCollector('foo'));
    }

    public function testGetAllCollectors()
    {
        $collector1 = $this->getMock('\Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface');
        $nameCollector1 = 'collector1';
        $collector1
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($nameCollector1));
        $collector2 = $this->getMock('\Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface');
        $nameCollector2 = 'collector2';
        $collector2
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($nameCollector2));

        $allCollectors = [
            $nameCollector1 => $collector1,
            $nameCollector2 => $collector2,
        ];

        foreach ($allCollectors as $name => $collector) {
            $this->mainCollector->addCollector($collector);
        }

        $this->assertSame($allCollectors, $this->mainCollector->getAllCollectors());
    }

    public function testGetToolbarTemplateNothing()
    {
        $collector = $this->getMock('\Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface');
        $name = 'foobar';
        $collector
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($name));
        $this->mainCollector->addCollector($collector);
        $this->assertNull($this->mainCollector->getToolbarTemplate($name));
    }

    public function testGetToolbarTemplate()
    {
        $collector = $this->getMock('\Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface');
        $name = 'foobar';
        $collector
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($name));
        $toolbarTemplate = 'toolbar.html.twig';

        $this->mainCollector->addCollector($collector, 'foo', $toolbarTemplate);
        $this->assertSame($toolbarTemplate, $this->mainCollector->getToolbarTemplate($name));
    }

    public function testGetPanelTemplateNothing()
    {
        $collector = $this->getMock('\Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface');
        $name = 'foobar';
        $collector
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($name));
        $this->mainCollector->addCollector($collector);
        $this->assertNull($this->mainCollector->getPanelTemplate($name));
    }

    public function testGetPanelTemplate()
    {
        $collector = $this->getMock('\Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface');
        $name = 'foobar';
        $collector
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($name));
        $panelTemplate = 'toolbar.html.twig';

        $this->mainCollector->addCollector($collector, $panelTemplate, 'foo');
        $this->assertSame($panelTemplate, $this->mainCollector->getPanelTemplate($name));
    }

    public function testCollect()
    {
        $collector1 = $this->getMock('\Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface');
        $nameCollector1 = 'collector1';
        $collector1
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($nameCollector1));
        $collector2 = $this->getMock('\Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface');
        $nameCollector2 = 'collector2';
        $collector2
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($nameCollector2));

        $allCollectors = [
            $nameCollector1 => $collector1,
            $nameCollector2 => $collector2,
        ];

        $request = new Request();
        $response = new Response();
        $exception = new Exception();

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject
         */
        foreach ($allCollectors as $name => $collector) {
            $this->mainCollector->addCollector($collector);
            $collector
                ->expects($this->once())
                ->method('collect')
                ->with($request, $response, $exception);
        }

        $this->mainCollector->collect($request, $response, $exception);
    }
}
