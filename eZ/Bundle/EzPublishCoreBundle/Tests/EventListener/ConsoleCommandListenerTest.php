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
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConsoleCommandListenerTest extends TestCase
{
    /**
     * @var array
     */
    private $siteAccessList = ['default', 'site1'];

    /**
     * @var SiteAccess
     */
    private $siteAccess;

    /**
     * @var EventDispatcherInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $dispatcher;

    /**
     * @var ConsoleCommandListener
     */
    private $listener;

    /**
     * @var InputDefinition;
     */
    private $inputDefinition;

    /**
     * @var Command
     */
    private $command;

    /**
     * @var TestOutput
     */
    private $testOutput;

    public function setUp()
    {
        parent::setUp();
        $this->siteAccess = new SiteAccess();
        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->listener = new ConsoleCommandListener('default', $this->siteAccessList, $this->dispatcher);
        $this->listener->setSiteAccess($this->siteAccess);
        $this->dispatcher->addSubscriber($this->listener);
        $this->command = new Command('test:siteaccess');
        $this->inputDefinition = new InputDefinition([new InputOption('siteaccess', null, InputOption::VALUE_OPTIONAL)]);
        $this->testOutput = new TestOutput(Output::VERBOSITY_QUIET, true);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [
                ConsoleEvents::COMMAND => [['onConsoleCommand', -1]],
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
        $event = new ConsoleCommandEvent($this->command, $input, $this->testOutput);
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
        $event = new ConsoleCommandEvent($this->command, $input, $this->testOutput);
        $this->listener->setDebug(false);
        $this->listener->onConsoleCommand($event);
    }

    public function testValidSiteAccess()
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch');
        $input = new ArrayInput(['--siteaccess' => 'site1'], $this->inputDefinition);
        $event = new ConsoleCommandEvent($this->command, $input, $this->testOutput);
        $this->listener->onConsoleCommand($event);
        $this->assertEquals(new SiteAccess('site1', 'cli'), $this->siteAccess);
    }

    public function testDefaultSiteAccess()
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch');
        $input = new ArrayInput([], $this->inputDefinition);
        $event = new ConsoleCommandEvent($this->command, $input, $this->testOutput);
        $this->listener->onConsoleCommand($event);
        $this->assertEquals(new SiteAccess('default', 'cli'), $this->siteAccess);
    }
}
