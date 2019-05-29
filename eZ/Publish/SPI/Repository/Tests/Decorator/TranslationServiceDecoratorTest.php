<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Tests\Decorator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\TranslationService;
use eZ\Publish\API\Repository\Values\Translation;
use eZ\Publish\SPI\Repository\Decorator\TranslationServiceDecorator;

class TranslationServiceDecoratorTest extends TestCase
{
    protected function createDecorator(MockObject $service): TranslationService
    {
        return new class($service) extends TranslationServiceDecorator {
        };
    }

    protected function createServiceMock(): MockObject
    {
        return $this->createMock(TranslationService::class);
    }

    public function testTranslateDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Translation::class),
            'random_value_5ced05ce16efc3.57825052',
        ];

        $serviceMock->expects($this->exactly(1))->method('translate')->with(...$parameters);

        $decoratedService->translate(...$parameters);
    }

    public function testTranslateStringDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce16f054.25850298',
            'random_value_5ced05ce16f065.78328330',
        ];

        $serviceMock->expects($this->exactly(1))->method('translateString')->with(...$parameters);

        $decoratedService->translateString(...$parameters);
    }
}
