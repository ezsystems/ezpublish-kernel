<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\LimitationService as APILimitationService;
use eZ\Publish\Core\SignalSlot\LimitationService;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\SPI\Limitation\Type as LimitationInterface;

class LimitationServiceTest extends ServiceTest
{
    /**
     * This method is a dataProvider for \eZ\Publish\Core\SignalSlot\Tests\ServiceTest::testService.
     *
     * Should return array in following format:
     *
     *```php
     * [
     *     [
     *         $originalServiceMethodName,
     *         $methodArguments
     *         $returnedValueFromServiceMethod
     *         $numberOfSignalsEmitted,
     *         $signalClass = '',
     *         array $signalArguments = null
     *     ],
     *     ...
     * ]
     *
     * ```
     *
     * @return array
     */
    public function serviceProvider(): array
    {
        return [
            [
                'getLimitationType',
                [Limitation::LOCATION],
                $this->createMock(LimitationInterface::class),
                0,
            ],
            [
                'validateLimitations',
                [[new Limitation\LocationLimitation(['limitationValues' => [2]])]],
                [],
                0,
            ],
            [
                'validateLimitation',
                [new Limitation\LocationLimitation(['limitationValues' => [2]])],
                [],
                0,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getServiceMock()
    {
        return $this->createMock(APILimitationService::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSignalSlotService($innerService, SignalDispatcher $dispatcher)
    {
        return new LimitationService($innerService, $dispatcher);
    }
}
