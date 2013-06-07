<?php
/**
 * File containing the eZ\Publish\API\Repository\FieldTypeService class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\API\Repository
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\FieldTypeService as FieldTypeServiceInterface;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\SPI\Persistence\Handler;
use eZ\Publish\SPI\FieldType\FieldType as SPIFieldType;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Repository\Values\ContentType\FieldType;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use ArrayObject;

/**
 * An implementation of this class provides access to FieldTypes
 *
 * @package eZ\Publish\API\Repository
 * @see eZ\Publish\API\Repository\FieldType
 */
class FieldTypeService implements FieldTypeServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * @var array|\ArrayObject Hash of SPI FieldTypes or callable callbacks to generate one.
     */
    protected $settings;

    /**
     * Holds an array of FieldType objects
     *
     * @var \eZ\Publish\API\Repository\FieldType[]
     */
    protected $fieldTypes = array();

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     * @param array|\ArrayObject $settings Hash of SPI FieldTypes or callable callbacks to generate one.
     */
    public function __construct( RepositoryInterface $repository, Handler $handler, $settings = array() )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;

        if ( !$settings instanceof ArrayObject && !is_array( $settings ) )
        {
            throw new InvalidArgumentType( '\$settings', 'array|\ArrayObject', $settings );
        }

        $this->settings = $settings;
    }

    /**
     * Returns a list of all field types.
     *
     * @return \eZ\Publish\API\Repository\FieldType[]
     */
    public function getFieldTypes()
    {
        // Get array copy in case of ArrayObject
        if ( $this->settings instanceof ArrayObject )
            $array = $this->settings->getArrayCopy();
        else
            $array = $this->settings;

        foreach ( array_keys( $array ) as $identifier )
        {
            if ( isset( $this->fieldTypes[$identifier] ) )
                continue;

            $this->fieldTypes[$identifier] = $this->getFieldType( $identifier );
        }

        return $this->fieldTypes;
    }

    /**
     * Returns the FieldType registered with the given identifier
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\FieldType
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *         if there is no FieldType registered with $identifier
     */
    public function getFieldType( $identifier )
    {
        if ( isset( $this->fieldTypes[$identifier] ) )
        {
            return $this->fieldTypes[$identifier];
        }

        return ( $this->fieldTypes[$identifier] = new FieldType( $this->buildFieldType( $identifier ) ) );
    }

    /**
     * Returns if there is a FieldType registered under $identifier
     *
     * @param string $identifier
     *
     * @return boolean
     */
    public function hasFieldType( $identifier )
    {
        return isset( $this->settings[$identifier] );
    }

    /**
     * Instantiates a FieldType\Type object
     *
     * @todo Move this to a internal service provided to services that needs this (including this)
     *
     * @access private This function is for internal use only.
     *
     * @param string $identifier
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     *
     * @return \eZ\Publish\SPI\FieldType\FieldType
     */
    public function buildFieldType( $identifier )
    {
        if ( !isset( $this->settings[$identifier] ) )
        {
            // Get array copy in case of ArrayObject
            if ( $this->settings instanceof ArrayObject )
                $array = $this->settings->getArrayCopy();
            else
                $array = $this->settings;

            throw new NotFoundException(
                "FieldType",
                "Provided \$identifier is unknown: '{$identifier}', has: " . var_export( array_keys( $array ), true )
            );
        }

        $spiFieldType = $this->settings[$identifier];
        if ( $spiFieldType instanceof SPIFieldType )
        {
            return $spiFieldType;
        }
        else if ( !is_callable( $spiFieldType ) )
        {
            throw new InvalidArgumentException( "\$settings[$identifier]", 'must be instance of SPI\\FieldType\\FieldType or callback to generate it' );
        }

        /** @var $spiFieldType \Closure */
        return $spiFieldType();
    }
}
