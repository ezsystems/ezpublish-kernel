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
class ContentService implements \ezx\base\Interfaces\Service
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * Setups current instance with reference to repository object that created it.
     *
     * @param \ezx\base\Interfaces\Repository $repository
     */
    public function __construct( \ezx\base\Interfaces\Repository $repository )
    {
        $this->repository = $repository;
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
        $content = $this->repository->em->find( "ezx\content\Content", (int) $id );
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
        $type = $this->repository->ContentTypeService()->loadByIdentifier( $typeIdentifier );
        return new Content( $type );
    }
}
