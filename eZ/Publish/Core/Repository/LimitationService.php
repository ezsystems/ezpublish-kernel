<?php

/**
 * File containing LimitationService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\Core\Base\Exceptions\NotFound\LimitationNotFoundException;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\API\Repository\LimitationService as LimitationServiceInterface;
use eZ\Publish\SPI\Limitation\Type;

/**
 * This service provides methods for managing Limitations.
 */
class LimitationService implements LimitationServiceInterface
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @param array $settings
     */
    public function __construct(array $settings = array())
    {
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + array('limitationTypes' => array());
    }

    /**
     * {@inheritdoc}
     */
    public function getLimitationType($identifier): Type
    {
        if (!isset($this->settings['limitationTypes'][$identifier])) {
            throw new LimitationNotFoundException($identifier);
        }

        return $this->settings['limitationTypes'][$identifier];
    }

    /**
     * {@inheritdoc}
     */
    public function validateLimitations(array $limitations): array
    {
        $allErrors = array();
        foreach ($limitations as $limitation) {
            $errors = $this->validateLimitation($limitation);
            if (!empty($errors)) {
                $allErrors[$limitation->getIdentifier()] = $errors;
            }
        }

        return $allErrors;
    }

    /**
     * {@inheritdoc}
     */
    public function validateLimitation(Limitation $limitation): array
    {
        $identifier = $limitation->getIdentifier();
        if (!isset($this->settings['limitationTypes'][$identifier])) {
            throw new BadStateException(
                '$identifier',
                "limitationType[{$identifier}] is not configured"
            );
        }

        /** @var \eZ\Publish\SPI\Limitation\Type $type */
        $type = $this->settings['limitationTypes'][$identifier];

        // This will throw if it does not pass
        $type->acceptValue($limitation);

        // This return array of validation errors
        return $type->validate($limitation);
    }
}
