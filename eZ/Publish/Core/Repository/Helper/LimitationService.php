<?php
/**
 * File containing LimitationService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Helper;


use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\Core\Base\Exceptions\NotFound\LimitationNotFoundException;
use eZ\Publish\Core\Base\Exceptions\BadStateException;

/**
 * Internal service to deal with limitations and limitation types
 *
 * @package eZ\Publish\Core\Repository
 */
class LimitationService
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param array $settings
     */
    public function __construct( array $settings = array() )
    {
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + array( 'limitationTypes' => array() );
    }

    /**
     * Returns the LimitationType registered with the given identifier
     *
     * Returns the correct implementation of API Limitation value object
     * based on provided identifier
     *
     * @param string $identifier
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFound\LimitationNotFoundException
     * @return \eZ\Publish\SPI\Limitation\Type
     */
    public function getLimitationType( $identifier )
    {
        if ( !isset( $this->settings['limitationTypes'][$identifier] ) )
            throw new LimitationNotFoundException( $identifier );

        return $this->settings['limitationTypes'][$identifier];
    }

    /**
     * Validates an array of Limitations.
     *
     * @uses validateLimitation()
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation[] $limitations
     *
     * @return \eZ\Publish\Core\FieldType\ValidationError[][]
     */
    public function validateLimitations( array $limitations )
    {
        $allErrors = array();
        foreach ( $limitations as $limitation )
        {
            $errors = $this->validateLimitation( $limitation );
            if ( !empty( $errors ) )
            {
                $allErrors[$limitation->getIdentifier()] = $errors;
            }
        }

        return $allErrors;
    }

    /**
     * Validates single Limitation.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\BadStateException If the Role settings is in a bad state
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     *
     * @return \eZ\Publish\Core\FieldType\ValidationError[]
     */
    public function validateLimitation( Limitation $limitation )
    {
        $identifier = $limitation->getIdentifier();
        if ( !isset( $this->settings['limitationTypes'][$identifier] ) )
        {
            throw new BadStateException(
                '$identifier',
                "limitationType[{$identifier}] is not configured"
            );
        }

        /**
         * @var $type \eZ\Publish\SPI\Limitation\Type
         */
        $type = $this->settings['limitationTypes'][$identifier];

        // This will throw if it does not pass
        $type->acceptValue( $limitation );

        // This return array of validation errors
        return $type->validate( $limitation );
    }
}
