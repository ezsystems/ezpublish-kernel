<?php
/**
 * File containing the SectionHandler implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content\Section\Handler as SectionHandlerInterface;
use eZ\Publish\Core\Persistence\Factory as PersistenceFactory;
use Tedivm\StashBundle\Service\CacheService;

/**
 * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
 *
 * @todo Consider loadAll & loadByIdentifier cache, however then loadAll() must be used
 *       by all (incl create) but update & delete to avoid doing several cache lookups.
 */
class SectionHandler implements SectionHandlerInterface
{
    /**
     * @var \Tedivm\StashBundle\Service\CacheService
     */
    protected $cache;

    /**
     * @var \eZ\Publish\Core\Persistence\Factory
     */
    protected $persistenceFactory;

    /**
     * Setups current handler with everything needed
     *
     * @param \Tedivm\StashBundle\Service\CacheService $cache
     * @param \eZ\Publish\Core\Persistence\Factory $persistenceFactory
     */
    public function __construct( CacheService $cache, PersistenceFactory $persistenceFactory )
    {
        $this->cache = $cache;
        $this->persistenceFactory = $persistenceFactory;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function create( $name, $identifier )
    {
        $section = $this->persistenceFactory->getSectionHandler()->create( $name, $identifier );
        $this->cache->get( 'section', $section->id )->set( $section );
        return $section;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function update( $id, $name, $identifier )
    {
        $this->cache
            ->get( 'section', $id )
            ->set( $section = $this->persistenceFactory->getSectionHandler()->update( $id, $name, $identifier ) );
        return $section;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function load( $id )
    {
        $cache = $this->cache->get( 'section', $id );
        $section = $cache->get();
        if ( $cache->isMiss() )
            $cache->set( $section = $this->persistenceFactory->getSectionHandler()->load( $id ) );

        return $section;
    }

    /**
     * Get all section data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section[]
     */
    public function loadAll()
    {
        return $this->persistenceFactory->getSectionHandler()->loadAll();
    }

    /**
     * Get section data by identifier
     *
     * @param string $identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If section is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     */
    public function loadByIdentifier( $identifier )
    {
        return $this->persistenceFactory->getSectionHandler()->loadByIdentifier( $identifier );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function delete( $id )
    {
        $return = $this->persistenceFactory->getSectionHandler()->delete( $id );

        $this->cache->clear( 'section', $id );
        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function assign( $sectionId, $contentId )
    {
        return $this->persistenceFactory->getSectionHandler()->assign( $sectionId, $contentId );
    }

    /**
     * Number of content assignments a Section has
     *
     * @param mixed $sectionId
     *
     * @return int
     */
    public function assignmentsCount( $sectionId )
    {
        return $this->persistenceFactory->getSectionHandler()->assignmentsCount( $sectionId );
    }
}
