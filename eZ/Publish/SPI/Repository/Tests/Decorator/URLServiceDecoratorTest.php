<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Tests\Decorator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\URLService;
use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;
use eZ\Publish\SPI\Repository\Decorator\URLServiceDecorator;

class URLServiceDecoratorTest extends TestCase
{
    protected function createDecorator(MockObject $service): URLService
    {
        return new class($service) extends URLServiceDecorator {
        };
    }

    protected function createServiceMock(): MockObject
    {
        return $this->createMock(URLService::class);
    }

    public function testCreateUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->once())->method('createUpdateStruct')->with(...$parameters);

        $decoratedService->createUpdateStruct(...$parameters);
    }

    public function testFindUrlsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(URLQuery::class)];

        $serviceMock->expects($this->once())->method('findUrls')->with(...$parameters);

        $decoratedService->findUrls(...$parameters);
    }

    public function testFindUsagesDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(URL::class),
            10,
            100,
        ];

        $serviceMock->expects($this->once())->method('findUsages')->with(...$parameters);

        $decoratedService->findUsages(...$parameters);
    }

    public function testLoadByIdDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [1];

        $serviceMock->expects($this->once())->method('loadById')->with(...$parameters);

        $decoratedService->loadById(...$parameters);
    }

    public function testLoadByUrlDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce172635.77719845'];

        $serviceMock->expects($this->once())->method('loadByUrl')->with(...$parameters);

        $decoratedService->loadByUrl(...$parameters);
    }

    public function testUpdateUrlDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(URL::class),
            $this->createMock(URLUpdateStruct::class),
        ];

        $serviceMock->expects($this->once())->method('updateUrl')->with(...$parameters);

        $decoratedService->updateUrl(...$parameters);
    }
}
