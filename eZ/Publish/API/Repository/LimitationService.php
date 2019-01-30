<?php

/**
 * File containing the eZ\Publish\API\Repository\LimitationService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace  eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\SPI\Limitation\Type;

/**
 * This service provides methods for managing Limitations.
 */
interface LimitationService
{
    /**
     * Returns the LimitationType registered with the given identifier.
     *
     * Returns the correct implementation of API Limitation value object
     * based on provided identifier
     *
     * @param string $identifier
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFound\LimitationNotFoundException
     *
     * @return \eZ\Publish\SPI\Limitation\Type
     */
    public function getLimitationType($identifier): Type;

    /**
     * Validates an array of Limitations.
     *
     * @uses ::validateLimitation()
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation[] $limitations
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\BadStateException If the Role settings is in a bad state
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[][]
     */
    public function validateLimitations(array $limitations): array;

    /**
     * Validates single Limitation.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\BadStateException If the Role settings is in a bad state
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If the Role settings is in a bad state
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateLimitation(Limitation $limitation): array;
}
