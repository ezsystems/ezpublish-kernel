<?php

/**
 * LimitationService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\LimitationService as LimitationServiceInterface;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\SPI\Limitation\Type;

class LimitationService implements LimitationServiceInterface
{
    /**
     * Aggregated service.
     *
     * @var \eZ\Publish\API\Repository\LimitationService
     */
    protected $service;

    /**
     * SignalDispatcher.
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Construct service object from aggregated service and signal dispatcher.
     *
     * @param \eZ\Publish\API\Repository\LimitationService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct(LimitationServiceInterface $service, SignalDispatcher $signalDispatcher)
    {
        $this->service = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimitationType($identifier): Type
    {
        return $this->service->getLimitationType($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function validateLimitations(array $limitations): array
    {
        return $this->service->validateLimitations($limitations);
    }

    /**
     * {@inheritdoc}
     */
    public function validateLimitation(Limitation $limitation): array
    {
        return $this->service->validateLimitation($limitation);
    }
}
