<?php
/**
 * Content Service, extends repository with content specific operations
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

/**
 * Content Service, extends repository with content specific operations
 */
namespace ezx\content;
class ContentTypeService implements \ezx\base\Interfaces\Service
{
    /**
     * @var \ezx\base\Interfaces\Repository
     */
    protected $repository;

    /**
     * @var \ezx\base\Interfaces\StorageEngine\Handler
     */
    protected $handler;

    /**
     * Setups service with reference to repository object that created it & corresponding storage engine handler
     *
     * @param \ezx\base\Interfaces\Repository $repository
     * @param \ezx\base\Interfaces\StorageEngine\Handler $handler
     */
    public function __construct( \ezx\base\Interfaces\Repository $repository,
                                 \ezx\base\Interfaces\StorageEngine\Handler $handler )
    {
        $this->repository = $repository;
        $this->handler = $handler;
    }

    /**
     * Get an ContentType object by id
     *
     * @param int $id
     * @return ContentType
     * @throws \InvalidArgumentException
     */
    public function load( $id )
    {
        $contentType = $this->handler->load( $id );
        if ( !$contentType )
            throw new \InvalidArgumentException( "Could not find 'ContentType' with id: {$id}" );
        return $contentType;
    }

    /**
     * Get an ContentType by identifier
     *
     * @param string $identifier
     * @return ContentType
     * @throws \InvalidArgumentException
     */
    public function loadByIdentifier( $identifier )
    {
        $contentTypes = $this->handler->loadByIdentifier( $identifier );
        if ( !$contentTypes )
            throw new \InvalidArgumentException( "Could not find 'ContentType' with identifier: {$identifier}" );
        return $contentTypes[0];
    }
}
