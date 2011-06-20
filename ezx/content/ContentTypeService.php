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
     * @var \ezx\base\Interfaces\StorageEngine
     */
    protected $se;

    /**
     * Setups current instance with reference to repository object that created it.
     *
     * @param \ezx\base\Interfaces\Repository $repository
     * @param \ezx\base\Interfaces\StorageEngine $se
     */
    public function __construct( \ezx\base\Interfaces\Repository $repository, \ezx\base\Interfaces\StorageEngine $se )
    {
        $this->repository = $repository;
        $this->se = $se;
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
        $contentType = $this->se->ContentTypeHandler()->load( $id );
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
        $contentTypes = $this->se->ContentTypeHandler()->loadByIdentifier( $identifier );
        if ( !$contentTypes )
            throw new \InvalidArgumentException( "Could not find 'ContentType' with identifier: {$identifier}" );
        return $contentTypes[0];
    }
}
