<?php
/**
 * File containing the eZ\Publish\API\Repository\FieldTypeService class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\API\Repository
 */

namespace eZ\Publish\Core\Repository;
use eZ\Publish\API\Repository\FieldTypeService as FieldTypeServiceInterface,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\SPI\Persistence\Handler,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZ\Publish\Core\FieldType;

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
     * @var array
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
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $handler, array $settings = array() )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
        $this->settings = $settings;
    }

    /**
     * Returns a list of all field types.
     *
     * @return \eZ\Publish\API\Repository\FieldType[]
     */
    public function getFieldTypes()
    {

    }

    /**
     * Returns the FieldType registered with the given identifier
     *
     * @param string $identifier
     * @return \eZ\Publish\API\Repository\FieldType
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *         if there is no FieldType registered with $identifier
     */
    public function getFieldType( $identifier )
    {
        if ( isset( $this->fieldTypes[$identifier] ) )
        {
            $this->fieldTypes[$identifier];
        }

        $this->fieldTypes[$identifier] = new FieldType( $this->buildFieldType( $identifier ) );

        return $this->fieldTypes[$identifier];
    }

    /**
     * Returns if there is a FieldType registered under $identifier
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function hasFieldType( $identifier )
    {
        return isset( $this->settings[$identifier] );
    }

    /**
     * Instantiates a FieldType\Type object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $type not priorly setup
     *         with settings injected to service
     *
     * @param string $type
     * @return \eZ\Publish\SPI\FieldType\FieldType
     */
    public function buildFieldType( $identifier )
    {
        if ( !isset( $this->settings[$identifier] ) )
        {
            throw new NotFoundException(
                "FieldType",
                "Provided \$identifier is unknown: '{$identifier}', has: " . var_export( array_keys( $this->settings ), true )
            );
        }

        /** @var $closure \Closure */
        $closure = $this->settings[$identifier];
        return $closure();
    }
}
