<?php
/**
 * Content Service
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
class ContentService implements \ezp\base\ServiceInterface
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
     * Get an Content object by id
     *
     * @param int $id
     * @return Content
     * @throws \InvalidArgumentException
     */
    public function load( $id )
    {
        $content = $this->se->getContentHandler()->load( $id );
        if ( !$content )
            throw new \InvalidArgumentException( "Could not find 'Content' with id: {$id}" );
        return $content;
    }

    /**
     * Create content object
     *
     * @uses ContentTypeService::loadByIdentifier()
     * @param string $typeIdentifier
     * @return Content
     */
    public function create( $typeIdentifier )
    {
        $type = $this->repository->getContentTypeService()->loadByIdentifier( $typeIdentifier );
        return new Content( $type );
    }
}
