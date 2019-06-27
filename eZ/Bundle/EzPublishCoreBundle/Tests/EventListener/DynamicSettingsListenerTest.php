<?php

/**
 * File containing the DynamicSettingsListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\DynamicSettingsListener;
use eZ\Bundle\EzPublishCoreBundle\Tests\EventListener\Stubs\FooServiceInterface;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ExpressionLanguage;

class DynamicSettingsListenerTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $container;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $expressionLanguage;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->createMock(ContainerInterface::class);
        $this->expressionLanguage = $this->createMock(ExpressionLanguage::class);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [
                MVCEvents::SITEACCESS => ['onSiteAccessMatch', 254],
                MVCEvents::CONFIG_SCOPE_CHANGE => ['onConfigScopeChange', 90],
                MVCEvents::CONFIG_SCOPE_RESTORE => ['onConfigScopeChange', 90],
            ],
            DynamicSettingsListener::getSubscribedEvents()
        );
    }

    public function testOnSiteAccessMatchSubRequest()
    {
        $event = new PostSiteAccessMatchEvent(new SiteAccess('test'), new Request(), HttpKernelInterface::SUB_REQUEST);
        $resettableServices = ['foo', 'bar.baz'];
        $updateableServices = [
            'some_service' => [['method' => 'some_expression']],
            'another_service' => [['method' => 'another_expression']],
        ];

        $this->container
            ->expects($this->never())
            ->method('set');
        $this->expressionLanguage
            ->expects($this->never())
            ->method('evaluate');

        $listener = new DynamicSettingsListener($resettableServices, $updateableServices, $this->expressionLanguage);
        $listener->setContainer($this->container);
        $listener->onSiteAccessMatch($event);
    }

    public function testOnSiteAccessMatch()
    {
        $event = new PostSiteAccessMatchEvent(new SiteAccess('test'), new Request(), HttpKernelInterface::MASTER_REQUEST);
        $resettableServices = ['foo', 'bar.baz'];
        $updateableServices = [
            'some_service' => [['someMethod', 'some_expression']],
            'another_service' => [['someMethod', 'another_expression']],
        ];

        $this->container
            ->expects($this->at(0))
            ->method('initialized')
            ->willReturn(true);
        $this->container
            ->expects($this->at(1))
            ->method('set')
            ->with('foo', null);
        $this->container
            ->expects($this->at(2))
            ->method('initialized')
            ->willReturn(true);
        $this->container
            ->expects($this->at(3))
            ->method('set')
            ->with('bar.baz', null);

        $updateableService1 = $this->createMock(FooServiceInterface::class);
        $dynamicSetting1 = 'foo';
        $this->container
            ->expects($this->at(4))
            ->method('initialized')
            ->willReturn(true);
        $this->container
            ->expects($this->at(5))
            ->method('get')
            ->with('some_service')
            ->willReturn($updateableService1);
        $updateableService1
            ->expects($this->once())
            ->method('someMethod')
            ->with($dynamicSetting1);

        $updateableService2 = $this->createMock(FooServiceInterface::class);
        $dynamicSetting2 = ['foo' => 'bar'];
        $this->container
            ->expects($this->at(6))
            ->method('initialized')
            ->willReturn(true);
        $this->container
            ->expects($this->at(7))
            ->method('get')
            ->with('another_service')
            ->willReturn($updateableService2);
        $updateableService2
            ->expects($this->once())
            ->method('someMethod')
            ->with($dynamicSetting2);

        $this->expressionLanguage
            ->expects($this->exactly(count($updateableServices)))
            ->method('evaluate')
            ->willReturnMap(
                [
                    ['some_expression', ['container' => $this->container], $dynamicSetting1],
                    ['another_expression', ['container' => $this->container], $dynamicSetting2],
                ]
            );

        $listener = new DynamicSettingsListener($resettableServices, $updateableServices, $this->expressionLanguage);
        $listener->setContainer($this->container);
        $listener->onSiteAccessMatch($event);
    }

    public function testOnConfigScopeChange()
    {
        $siteAccess = new SiteAccess('test');
        $event = new ScopeChangeEvent($siteAccess);
        $resettableServices = ['foo', 'bar.baz'];
        $updateableServices = [
            'some_service' => [['someMethod', 'some_expression']],
            'another_service' => [['someMethod', 'another_expression']],
        ];

        $this->container
            ->expects($this->at(0))
            ->method('initialized')
            ->willReturn(true);
        $this->container
            ->expects($this->at(1))
            ->method('set')
            ->with('foo', null);
        $this->container
            ->expects($this->at(2))
            ->method('initialized')
            ->willReturn(true);
        $this->container
            ->expects($this->at(3))
            ->method('set')
            ->with('bar.baz', null);

        $updateableService1 = $this->createMock(FooServiceInterface::class);
        $dynamicSetting1 = 'foo';
        $this->container
            ->expects($this->at(4))
            ->method('initialized')
            ->willReturn(true);
        $this->container
            ->expects($this->at(5))
            ->method('get')
            ->with('some_service')
            ->willReturn($updateableService1);
        $updateableService1
            ->expects($this->once())
            ->method('someMethod')
            ->with($dynamicSetting1);

        $updateableService2 = $this->createMock(FooServiceInterface::class);
        $dynamicSetting2 = ['foo' => 'bar'];
        $this->container
            ->expects($this->at(6))
            ->method('initialized')
            ->willReturn(true);
        $this->container
            ->expects($this->at(7))
            ->method('get')
            ->with('another_service')
            ->willReturn($updateableService2);
        $updateableService2
            ->expects($this->once())
            ->method('someMethod')
            ->with($dynamicSetting2);

        $this->expressionLanguage
            ->expects($this->exactly(count($updateableServices)))
            ->method('evaluate')
            ->willReturnMap(
                [
                    ['some_expression', ['container' => $this->container], $dynamicSetting1],
                    ['another_expression', ['container' => $this->container], $dynamicSetting2],
                ]
            );

        $listener = new DynamicSettingsListener($resettableServices, $updateableServices, $this->expressionLanguage);
        $listener->setContainer($this->container);
        $listener->onConfigScopeChange($event);
        $this->assertSame($siteAccess, $event->getSiteAccess());
    }
}
