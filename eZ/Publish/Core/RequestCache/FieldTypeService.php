<?php
/**
 * FieldTypeService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\RequestCache;

use \eZ\Publish\API\Repository\FieldTypeService as FieldTypeServiceInterface;

/**
 * FieldTypeService class
 * @package eZ\Publish\Core\RequestCache
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
     * CachePool
     *
     * @var \eZ\Publish\Core\RequestCache\CachePool
     */
    protected $cachePool;

    /**
     * Constructor
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\FieldTypeService $service
     * @param \eZ\Publish\Core\RequestCache\CachePool $cachePool
     */
    public function __construct( FieldTypeServiceInterface $service, CachePool $cachePool )
    {
        $this->service = $service;
        $this->cachePool = $cachePool;
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
     * @return bool
     */
    public function hasFieldType( $identifier )
    {
        return $this->service->hasFieldType( $identifier );
    }
}
