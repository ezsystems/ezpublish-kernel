<?php
/**
 * FieldTypeService class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\SignalSlot;

use eZ\Publish\API\Repository\FieldTypeService as FieldTypeServiceInterface;

/**
 * FieldTypeService class
 * @package eZ\Publish\Core\Repository\SignalSlot
 */
class FieldTypeService implements FieldTypeServiceInterface
{
    /**
     * Aggregated service
     *
     * @var \eZ\Publish\API\Repository\FieldTypeService
     */
    protected $service;

    /**
     * SignalDispatcher
     *
     * @var \eZ\Publish\Core\Repository\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\FieldTypeService $service
     * @param \eZ\Publish\Core\Repository\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct( FieldTypeServiceInterface $service, SignalDispatcher $signalDispatcher )
    {
        $this->service          = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * Returns a list of all field types.
     *
     * @return \eZ\Publish\API\Repository\FieldType[]
     */
    public function getFieldTypes()
    {
        return $this->service->getFieldTypes();
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
        return $this->service->getFieldType( $identifier );
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
        return $this->service->hasFieldType( $identifier );
    }

    /**
     * Instantiates a FieldType\Type object
     *
     * @todo Move this to a internal service provided to services that needs this (including this)
     *
     * @access private This function is for internal use only.
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $type not properly setup
     *         with settings injected to service
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\SPI\FieldType\FieldType
     */
    public function buildFieldType( $identifier )
    {
        return $this->service->buildFieldType( $identifier );
    }
}
