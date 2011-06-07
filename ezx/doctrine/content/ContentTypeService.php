<?php
/**
 * Content Service, extends repository with content specific operations
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 * Content Service, extends repository with content specific operations
 */
namespace ezx\doctrine\content;
class ContentTypeService implements \ezx\doctrine\Interface_Service
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * Setups current instance with reference to repository object that created it.
     *
     * @param Repository $repository
     */
    public function __construct( \ezx\doctrine\Interface_Repository $repository )
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
        $content = $this->repository->em->find( "ezx\doctrine\content\ContentType", (int) $id );
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
        $query = $this->repository->em->createQuery( "SELECT a FROM ezx\doctrine\content\ContentType a WHERE a.identifier = :identifier" );
        $query->setParameter( 'identifier', $identifier );
        $contentTypes = $query->getResult();
        if ( !$contentTypes )
            throw new \InvalidArgumentException( "Could not find 'ContentType' with identifier: {$identifier}" );
        return $contentTypes[0];
    }
}
