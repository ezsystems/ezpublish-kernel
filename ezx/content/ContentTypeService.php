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
class ContentTypeService implements \ezp\base\ServiceInterface
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
    public function __construct( \ezp\base\Repository $repository,
                                 \ezp\base\StorageEngineInterface $se )
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
        $contentType = $this->se->getContentTypeHandler()->load( $id );
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
        $contentTypes = $this->se->getContentTypeHandler()->loadByIdentifier( $identifier );
        if ( !$contentTypes )
            throw new \InvalidArgumentException( "Could not find 'ContentType' with identifier: {$identifier}" );
        return $contentTypes[0];
    }
}
