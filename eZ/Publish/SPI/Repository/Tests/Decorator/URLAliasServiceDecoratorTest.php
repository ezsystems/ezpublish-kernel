<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Tests\Decorator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\SPI\Repository\Decorator\URLAliasServiceDecorator;

class URLAliasServiceDecoratorTest extends TestCase
{
    private const EXAMPLE_OFFSET = 10;
    private const EXAMPLE_LIMIT = 100;

    protected function createDecorator(MockObject $service): URLAliasService
    {
        return new class($service) extends URLAliasServiceDecorator {
        };
    }

    protected function createServiceMock(): MockObject
    {
        return $this->createMock(URLAliasService::class);
    }

    public function testCreateUrlAliasDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Location::class),
            'random_value_5ced05ce0f45c8.98320978',
            'random_value_5ced05ce0f45f9.49337276',
            true,
            true,
        ];

        $serviceMock->expects($this->once())->method('createUrlAlias')->with(...$parameters);

        $decoratedService->createUrlAlias(...$parameters);
    }

    public function testCreateGlobalUrlAliasDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce0f4681.71978747',
            'random_value_5ced05ce0f4690.44246628',
            'random_value_5ced05ce0f46a4.07620211',
            true,
            true,
        ];

        $serviceMock->expects($this->once())->method('createGlobalUrlAlias')->with(...$parameters);

        $decoratedService->createGlobalUrlAlias(...$parameters);
    }

    public function testListLocationAliasesDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Location::class),
            true,
            'random_value_5ced05ce0f4720.32499208',
        ];

        $serviceMock->expects($this->once())->method('listLocationAliases')->with(...$parameters);

        $decoratedService->listLocationAliases(...$parameters);
    }

    public function testListGlobalAliasesDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce0f4757.52395035',
            self::EXAMPLE_OFFSET,
            self::EXAMPLE_LIMIT,
        ];

        $serviceMock->expects($this->once())->method('listGlobalAliases')->with(...$parameters);

        $decoratedService->listGlobalAliases(...$parameters);
    }

    public function testRemoveAliasesDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [['random_value_5ced05ce0f4797.71498070']];

        $serviceMock->expects($this->once())->method('removeAliases')->with(...$parameters);

        $decoratedService->removeAliases(...$parameters);
    }

    public function testLookupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce0f47c7.90507163',
            'random_value_5ced05ce0f47d7.99589118',
        ];

        $serviceMock->expects($this->once())->method('lookup')->with(...$parameters);

        $decoratedService->lookup(...$parameters);
    }

    public function testReverseLookupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Location::class),
            'random_value_5ced05ce0f4803.92292057',
        ];

        $serviceMock->expects($this->once())->method('reverseLookup')->with(...$parameters);

        $decoratedService->reverseLookup(...$parameters);
    }

    public function testLoadDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce0f4839.47843200'];

        $serviceMock->expects($this->once())->method('load')->with(...$parameters);

        $decoratedService->load(...$parameters);
    }

    public function testRefreshSystemUrlAliasesForLocationDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Location::class)];

        $serviceMock->expects($this->once())->method('refreshSystemUrlAliasesForLocation')->with(...$parameters);

        $decoratedService->refreshSystemUrlAliasesForLocation(...$parameters);
    }

    public function testDeleteCorruptedUrlAliasesDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->once())->method('deleteCorruptedUrlAliases')->with(...$parameters);

        $decoratedService->deleteCorruptedUrlAliases(...$parameters);
    }
}
