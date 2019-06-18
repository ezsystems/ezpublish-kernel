<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Tests\Decorator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\URLWildcardService;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;
use eZ\Publish\SPI\Repository\Decorator\URLWildcardServiceDecorator;

class URLWildcardServiceDecoratorTest extends TestCase
{
    protected function createDecorator(MockObject $service): URLWildcardService
    {
        return new class($service) extends URLWildcardServiceDecorator {
        };
    }

    protected function createServiceMock(): MockObject
    {
        return $this->createMock(URLWildcardService::class);
    }

    public function testCreateDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce0f76d9.50834569',
            'random_value_5ced05ce0f7709.88573575',
            'random_value_5ced05ce0f7713.40900633',
        ];

        $serviceMock->expects($this->once())->method('create')->with(...$parameters);

        $decoratedService->create(...$parameters);
    }

    public function testRemoveDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(URLWildcard::class)];

        $serviceMock->expects($this->once())->method('remove')->with(...$parameters);

        $decoratedService->remove(...$parameters);
    }

    public function testLoadDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce0f7e67.73670278'];

        $serviceMock->expects($this->once())->method('load')->with(...$parameters);

        $decoratedService->load(...$parameters);
    }

    public function testLoadAllDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce0f7ea4.77940790',
            'random_value_5ced05ce0f7eb2.19026311',
        ];

        $serviceMock->expects($this->once())->method('loadAll')->with(...$parameters);

        $decoratedService->loadAll(...$parameters);
    }

    public function testTranslateDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce0f7ee7.19290474'];

        $serviceMock->expects($this->once())->method('translate')->with(...$parameters);

        $decoratedService->translate(...$parameters);
    }
}
