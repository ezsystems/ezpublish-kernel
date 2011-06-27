<?php
/**
 * File containing the ezp\content\Services\ContentType class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */

namespace ezp\content\Services;

/**
 * Content Service, extends repository with content specific operations
 *
 * @package ezp
 * @subpackage content
 */
use ezp\content\ContentType, ezp\base\ServiceInterface, ezp\base\Repository, ezp\base\StorageEngineInterface;
class ContentType implements ServiceInterface
{
    /**
     * @var \ezp\base\Repository
     */
    protected $repository;

    /**
     * @var \ezp\base\StorageEngineInterface
     */
    protected $se;

    /**
     * Setups service with reference to repository object that created it & corresponding storage engine handler
     *
     * @param \ezp\base\Repository $repository
     * @param \ezp\base\StorageEngineInterface $se
     */
    public function __construct( Repository $repository,
                                 StorageEngineInterface $se )
    {
        $this->repository = $repository;
        $this->se = $se;
    }

    /**
     * Get an ContentType object by id
     *
     * @param int $contentTypeId
     * @return \ezp\content\ContentType
     * @throws \ezp\content\ContentNotFoundException
     */
    public function load( $contentTypeId )
    {
        $contentType = $this->se->getContentTypeHandler()->load( $contentTypeId );
        if ( !$contentType )
            throw new \ezp\content\ContentNotFoundException( $contentTypeId, 'ContentType' );
        return $contentType;
    }

    /**
     * Get an ContentType by identifier
     *
     * @param string $identifier
     * @return \ezp\content\ContentType
     * @throws \ezp\content\ContentNotFoundException
     */
    public function loadByIdentifier( $identifier )
    {
        $contentTypes = $this->se->getContentTypeHandler()->loadByIdentifier( $identifier );
        if ( !$contentTypes )
            throw new \ezp\content\ContentNotFoundException( $identifier, 'ContentType', 'identifier' );
        return $contentTypes[0];
    }
}
