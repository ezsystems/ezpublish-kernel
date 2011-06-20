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
     * Get an ContentType object by id
     *
     * @param int $id
     * @return ContentType
     * @throws \InvalidArgumentException
     */
    public function load( $id )
    {
        $content = $this->repository->em->find( "ezx\content\ContentType", (int) $id );
        if ( !$content )
            throw new \InvalidArgumentException( "Could not find 'ContentType' with id: {$id}" );
        return $content;
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
        $query = $this->repository->em->createQuery( "SELECT a FROM ezx\content\ContentType a WHERE a.identifier = :identifier" );
        $query->setParameter( 'identifier', $identifier );
        $contentTypes = $query->getResult();
        if ( !$contentTypes )
            throw new \InvalidArgumentException( "Could not find 'ContentType' with identifier: {$identifier}" );
        return $contentTypes[0];
    }
}
