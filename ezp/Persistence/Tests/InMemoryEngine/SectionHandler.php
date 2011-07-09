<?php
/**
 * File containing the SectionHandler implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage persistence_tests
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Tests\InMemoryEngine;

/**
 * @see \ezp\Persistence\Content\Interfaces\SectionHandler
 *
 * @package ezp
 * @subpackage persistence_tests
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
     * @param string $name
     * @param string $identifier
     * @return \ezp\Persistence\Content\Section
     */
    public function create( $name, $identifier )
    {
        return $this->backend->create( 'Content', 'Section', array( 'name' => $name,
                                                                    'identifier' => $identifier ) );
    }

    /**
     * @param $id
     * @param string $name
     * @param string $identifier
     * @return bool
     */
    public function update( $id, $name, $identifier )
    {
        return $this->backend->update( 'Content', 'Section', $id, array( 'id' => $id,
                                                                         'name' => $name,
                                                                         'identifier' => $identifier ) );
    }

    /**
     * @param int $id
     * @return \ezp\Persistence\Content\Section|null
     */
    public function load( $id )
    {
        return $this->backend->read( 'Content', 'Section', $id );
    }

    /**
     * @param int $id
     */
    public function delete( $id )
    {
        return $this->backend->delete( 'Content', 'Section', $id );
    }

    /**
     * @param int $sectionId
     * @param int $contentId
     */
    public function assign( $sectionId, $contentId )
    {
    }
}
?>
