<?php

/**
 * File containing the Role Limitation converter.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\User\Role;

use eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Limitation converter.
 *
 * Takes care of Converting a Policy limitation from Legacy value to spi value accepted by API.
 */
class LimitationConverter
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationHandler[] */
    protected $limitationHandlers;

    /**
     * Construct from LimitationConverter.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationHandler[] $limitationHandlers
     */
    public function __construct(array $limitationHandlers = [])
    {
        $this->limitationHandlers = $limitationHandlers;
    }

    /**
     * Adds handler.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationHandler $handler
     */
    public function addHandler(LimitationHandler $handler)
    {
        $this->limitationHandlers[] = $handler;
    }

    /**
     * @param Policy $policy
     */
    public function toLegacy(Policy $policy)
    {
        foreach ($this->limitationHandlers as $limitationHandler) {
            $limitationHandler->toLegacy($policy);
        }
    }

    /**
     * @param Policy $policy
     */
    public function toSPI(Policy $policy)
    {
        foreach ($this->limitationHandlers as $limitationHandler) {
            $limitationHandler->toSPI($policy);
        }
    }
}
