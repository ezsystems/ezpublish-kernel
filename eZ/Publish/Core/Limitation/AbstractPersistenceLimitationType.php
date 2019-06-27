<?php

/**
 * File containing the eZ\Publish\Core\Limitation\AbstractPersistenceLimitationType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Limitation;

use eZ\Publish\SPI\Persistence\Handler as SPIPersistenceHandler;

/**
 * LocationLimitation is a Content limitation.
 */
class AbstractPersistenceLimitationType
{
    /** @var \eZ\Publish\SPI\Persistence\Handler */
    protected $persistence;

    /**
     * @param \eZ\Publish\SPI\Persistence\Handler $persistence
     */
    public function __construct(SPIPersistenceHandler $persistence)
    {
        $this->persistence = $persistence;
    }
}
