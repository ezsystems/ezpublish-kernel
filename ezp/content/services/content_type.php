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
use ezp\base\Interfaces\Service, ezp\base\Repository;
class ContentType implements Service
{
    /**
     * @var \ezp\base\Repository
     */
    protected $repository;

    /**
     * @var \ezp\persistence\Interfaces\RepositoryHandler
     */
    protected $handler;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \ezp\base\Repository $repository
     * @param \ezp\persistence\Interfaces\RepositoryHandler $handler
     */
    public function __construct( Repository $repository, \ezp\persistence\Interfaces\RepositoryHandler $handler )
    {
        $this->repository = $repository;
        $this->handler = $handler;
    }

    /**
     * Get an Content Type object by id
     *
     * @param int $contentTypeId
     * @return \ezp\content\type\Type
     * @throws \ezp\content\ContentNotFoundException
     */
    public function load( $contentTypeId )
    {
        $contentType = $this->handler->contentTypeHandler()->load( $contentTypeId );
        if ( !$contentType )
            throw new \ezp\content\ContentNotFoundException( $contentTypeId, 'type\Type' );
        return $contentType;
    }

    /**
     * Get an Content Type by identifier
     *
     * @param string $identifier
     * @return \ezp\content\type\Type
     * @throws \ezp\content\ContentNotFoundException
     */
    public function loadByIdentifier( $identifier )
    {
        $contentTypes = $this->handler->contentTypeHandler()->loadByIdentifier( $identifier );
        if ( !$contentTypes )
            throw new \ezp\content\ContentNotFoundException( $identifier, 'type\Type', 'identifier' );
        return $contentTypes[0];
    }
}
