<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Permission;

use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\Core\Base\Exceptions\NotFound\LimitationNotFoundException;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\SPI\Limitation\Type;
use Traversable;

/**
 * Internal service to deal with limitations and limitation types.
 *
 * @internal Meant for internal use by Repository.
 */
class LimitationService
{
    /** @var \eZ\Publish\SPI\Limitation\Type[] */
    private $limitationTypes;

    public function __construct(?Traversable $limitationTypes = null)
    {
        $this->limitationTypes = null !== $limitationTypes
            ? iterator_to_array($limitationTypes) :
            [];
    }

    /**
     * Returns the LimitationType registered with the given identifier.
     *
     * Returns the correct implementation of API Limitation value object
     * based on provided identifier
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFound\LimitationNotFoundException
     */
    public function getLimitationType(string $identifier): Type
    {
        if (!isset($this->limitationTypes[$identifier])) {
            throw new LimitationNotFoundException($identifier);
        }

        return $this->limitationTypes[$identifier];
    }

    /**
     * Validates an array of Limitations.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation[] $limitations
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[][]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function validateLimitations(array $limitations): array
    {
        $allErrors = [];
        foreach ($limitations as $limitation) {
            $errors = $this->validateLimitation($limitation);
            if (!empty($errors)) {
                $allErrors[$limitation->getIdentifier()] = $errors;
            }
        }

        return $allErrors;
    }

    /**
     * Validates single Limitation.
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If the Role settings is in a bad state*@throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function validateLimitation(Limitation $limitation): array
    {
        $identifier = $limitation->getIdentifier();
        if (!isset($this->limitationTypes[$identifier])) {
            throw new BadStateException(
                '$identifier',
                "limitationType[{$identifier}] is not configured"
            );
        }

        $type = $this->limitationTypes[$identifier];

        // This will throw if it does not pass
        $type->acceptValue($limitation);

        // This return array of validation errors
        return $type->validate($limitation);
    }
}
