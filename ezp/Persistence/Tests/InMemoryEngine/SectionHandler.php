<?php
/**
 * File containing the SectionHandler implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Tests\InMemoryEngine;

/**
 * @see \ezp\Persistence\Content\Interfaces\SectionHandler
 *
 * @version //autogentag//
 */
class SectionHandler implements \ezp\Persistence\Content\Interfaces\SectionHandler
{
    /**
     * @var RepositoryHandler
     */
    protected $handler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to RepositoryHandler object that created it.
     *
     * @param RepositoryHandler $handler
     * @param Backend $backend The storage engine backend
     */
    public function __construct( RepositoryHandler $handler, Backend $backend )
    {
        $this->handler = $handler;
        $this->backend = $backend;
    }

    /**
     * @see \ezp\Persistence\Content\Interfaces\SectionHandler
     */
    public function create( $name, $identifier )
    {
        return $this->backend->create(
            'Content\Section',
            array(
                'name' => $name,
                'identifier' => $identifier
            )
        );
    }

    /**
     * @see \ezp\Persistence\Content\Interfaces\SectionHandler
     */
    public function update( $id, $name, $identifier )
    {
        return $this->backend->update(
            'Content\Section',
            $id,
            array(
                'id' => $id,
                'name' => $name,
                'identifier' => $identifier
            )
        );
    }

    /**
     * @see \ezp\Persistence\Content\Interfaces\SectionHandler
     */
    public function load( $id )
    {
        return $this->backend->load( 'Content\Section', $id );
    }

    /**
     * @see \ezp\Persistence\Content\Interfaces\SectionHandler
     */
    public function delete( $id )
    {
        return $this->backend->delete( 'Content\Section', $id );
    }

    /**
     * @see \ezp\Persistence\Content\Interfaces\SectionHandler
     */
    public function assign( $sectionId, $contentId )
    {
        // @todo Depends on working SubTree Criterion implementation.
    }
}
?>
