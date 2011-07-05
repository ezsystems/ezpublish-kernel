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

namespace ezp\persistence\tests\in_memory_engine;

/**
 * @see \ezp\persistence\content\SectionHandlerInterface
 *
 * @package ezp
 * @subpackage persistence_tests
 * @version //autogentag//
 */
class SectionHandler implements \ezp\persistence\content\SectionHandlerInterface
{
    /**
     * @var \ezp\persistence\tests\in_memory_engine\RepositoryHandler
     */
    protected $handler;

    /**
     * @var \ezp\persistence\tests\in_memory_engine\Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to storage engine object that created it.
     *
     * @param \ezp\persistence\RepositoryHandlerInterface $handler
     * @param \ezp\persistence\tests\in_memory_engine\Backend $backend Optional, use this argument if storage engine needs to pass backend object to handlers
     *                        to be able to handle operations.
     */
    public function __construct( \ezp\persistence\RepositoryHandlerInterface $handler, $backend = null )
    {
        $this->handler = $handler;
        $this->backend = $backend;
    }

	/**
	 * @param string $name
	 * @param string $identifier
	 * @return \ezp\persistence\content\Section
	 */
	public function create( $name, $identifier )
    {
        return $this->backend->create( 'content', 'Section', array( 'name' => $name,
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
        return $this->backend->update( 'content', 'Section', $id, array( 'id' => $id,
                                                                         'name' => $name,
                                                                         'identifier' => $identifier ) );
    }

    /**
	 * @param int $id
     * @return \ezp\persistence\content\Section|null
	 */
	public function load( $id )
    {
        return $this->backend->read( 'content', 'Section', $id );
    }

	/**
	 * @param int $id
	 */
	public function delete( $id )
    {
        return $this->backend->delete( 'content', 'Section', $id );
    }

	/**
	 * @param int $sectionId
	 * @param int $contentId
	 */
	public function assign( $sectionId, $contentId ){}
}
?>
