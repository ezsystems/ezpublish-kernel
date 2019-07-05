<?php

/**
 * File containing the ConsoleCommandListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\ConsoleCommandListener;
use eZ\Bundle\EzPublishCoreBundle\Tests\EventListener\Stubs\TestOutput;
use eZ\Publish\Core\MVC\Symfony\Event\ConsoleInitEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConsoleCommandListenerTest extends TestCase
{
    /** @var array */
    private $siteAccessList = ['default', 'site1'];

    /** @var SiteAccess */
    private $siteAccess;

    /** @var EventDispatcherInterface|MockObject */
    private $dispatcher;

    /** @var ConsoleCommandListener */
    private $listener;

    /** @var InputDefinition; */
    private $inputDefinition;

    /** @var TestOutput */
    private $testOutput;

    public function setUp()
    {
        parent::setUp();
        $this->siteAccess = new SiteAccess();
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->listener = new ConsoleCommandListener('default', $this->siteAccessList, $this->dispatcher);
        $this->listener->setSiteAccess($this->siteAccess);
        $this->dispatcher->addSubscriber($this->listener);
        $this->inputDefinition = new InputDefinition([new InputOption('siteaccess', null, InputOption::VALUE_OPTIONAL)]);
        $this->testOutput = new TestOutput(Output::VERBOSITY_QUIET, true);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [
                MVCEvents::CONSOLE_INIT => [['onConsoleCommand', -1]],
            ],
            $this->listener->getSubscribedEvents()
        );
    }

    /**
     * @expectedException \eZ\Publish\Core\MVC\Exception\InvalidSiteAccessException
     * @expectedExceptionMessageRegExp /^Invalid siteaccess 'foo', matched by .+\. Valid siteaccesses are/
     */
    public function testInvalidSiteAccessDev()
    {
        $this->dispatcher->expects($this->never())
            ->method('dispatch');
        $input = new ArrayInput(['--siteaccess' => 'foo'], $this->inputDefinition);
        $event = new ConsoleInitEvent($input, $this->testOutput);
        $this->listener->setDebug(true);
        $this->listener->onConsoleCommand($event);
    }

    /**
     * @expectedException \eZ\Publish\Core\MVC\Exception\InvalidSiteAccessException
     * @expectedExceptionMessageRegExp /^Invalid siteaccess 'foo', matched by .+\.$/
     */
    public function testInvalidSiteAccessProd()
    {
        $this->dispatcher->expects($this->never())
            ->method('dispatch');
        $input = new ArrayInput(['--siteaccess' => 'foo'], $this->inputDefinition);
        $event = new ConsoleInitEvent($input, $this->testOutput);
        $this->listener->setDebug(false);
        $this->listener->onConsoleCommand($event);
    }

    public function testValidSiteAccess()
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch');
        $input = new ArrayInput(['--siteaccess' => 'site1'], $this->inputDefinition);
        $event = new ConsoleInitEvent($input, $this->testOutput);
        $this->listener->onConsoleCommand($event);
        $this->assertEquals(new SiteAccess('site1', 'cli'), $this->siteAccess);
    }

    public function testDefaultSiteAccess()
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch');
        $input = new ArrayInput([], $this->inputDefinition);
        $event = new ConsoleInitEvent($input, $this->testOutput);
        $this->listener->onConsoleCommand($event);
        $this->assertEquals(new SiteAccess('default', 'cli'), $this->siteAccess);
    }
}
