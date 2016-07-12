<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\PermissionService as PermissionServiceInterface;
use eZ\Publish\API\Repository\Values\User\UserReference;
use eZ\Publish\API\Repository\Values\ValueObject;
use Closure;

/**
 * SignalSlot implementation of PermissionService interface.
 */
class PermissionService implements PermissionServiceInterface
{
    /**
     * Aggregated service.
     *
     * @var \eZ\Publish\API\Repository\PermissionService
     */
    protected $service;

    /**
     * SignalDispatcher.
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor.
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\PermissionService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct(PermissionServiceInterface $service, SignalDispatcher $signalDispatcher)
    {
        $this->service = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    public function getCurrentUserReference()
    {
        return $this->service->getCurrentUserReference();
    }

    public function setCurrentUserReference(UserReference $userReference)
    {
        return $this->service->setCurrentUserReference($userReference);
    }

    public function hasAccess($module, $function, UserReference $userReference = null)
    {
        return $this->service->hasAccess($module, $function, $userReference);
    }

    public function canUser($module, $function, ValueObject $object, $targets = null)
    {
        return $this->service->canUser($module, $function, $object, $targets);
    }

    public function sudo(Closure $callback)
    {
        return $this->service->sudo($callback, $this);
    }
}
