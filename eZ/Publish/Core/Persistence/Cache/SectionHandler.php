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

/**
 * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
 *
 * @todo Consider loadAll & loadByIdentifier cache, however then loadAll() must be used
 *       by all (incl create) but update & delete to avoid doing several cache lookups.
 */
class SectionHandler extends AbstractHandler implements SectionHandlerInterface
{
    /**
     * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function create( $name, $identifier )
    {
        $this->logger->logCall( __METHOD__, array( 'name' => $name, 'identifier' => $identifier ) );
        $section = $this->persistenceFactory->getSectionHandler()->create( $name, $identifier );
        $this->cache->getItem( 'section', $section->id )->set( $section );
        return $section;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function update( $id, $name, $identifier )
    {
        $this->logger->logCall( __METHOD__, array( 'section' => $id, 'name' => $name, 'identifier' => $identifier ) );
        $this->cache
            ->getItem( 'section', $id )
            ->set( $section = $this->persistenceFactory->getSectionHandler()->update( $id, $name, $identifier ) );
        return $section;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function load( $id )
    {
        $cache = $this->cache->getItem( 'section', $id );
        $section = $cache->get();
        if ( $cache->isMiss() )
        {
            $this->logger->logCall( __METHOD__, array( 'section' => $id ) );
            $cache->set( $section = $this->persistenceFactory->getSectionHandler()->load( $id ) );
        }

        return $section;
    }

    /**
     * Get all section data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section[]
     */
    public function loadAll()
    {
        $this->logger->logCall( __METHOD__ );
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
        $this->logger->logCall( __METHOD__, array( 'section' => $identifier ) );
        return $this->persistenceFactory->getSectionHandler()->loadByIdentifier( $identifier );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function delete( $id )
    {
        $this->logger->logCall( __METHOD__, array( 'section' => $id ) );
        $return = $this->persistenceFactory->getSectionHandler()->delete( $id );

        $this->cache->clear( 'section', $id );
        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function assign( $sectionId, $contentId )
    {
        $this->logger->logCall( __METHOD__, array( 'section' => $sectionId, 'content' => $contentId ) );
        $return = $this->persistenceFactory->getSectionHandler()->assign( $sectionId, $contentId );

        $this->cache->clear( 'content', 'info', $contentId );
        return $return;
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
        $this->logger->logCall( __METHOD__, array( 'section' => $sectionId ) );
        return $this->persistenceFactory->getSectionHandler()->assignmentsCount( $sectionId );
    }
}
