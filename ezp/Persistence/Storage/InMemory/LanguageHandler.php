<?php
/**
 * File containing the SectionHandler implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\InMemory;
use ezp\Persistence\Content\Language\Handler as LanguageHandlerInterface,
    ezp\Persistence\Content\Language,
    ezp\Persistence\Content\Language\CreateStruct;

/**
 * @see ezp\Persistence\Content\Section\Handler
 */
class LanguageHandler implements LanguageHandlerInterface
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
     * Create a new language
     *
     * @param \ezp\Persistence\Content\Language\CreateStruct $struct
     * @return \ezp\Persistence\Content\Language
     */
    public function create( CreateStruct $struct )
    {
        return $this->backend->create( 'Content\\Language', (array)$struct );
    }

    /**
     * Update language
     *
     * @param \ezp\Persistence\Content\Language $struct
     */
    public function update( Language $struct )
    {
        $this->backend->update(
            'Content\\Language',
            $struct->id,
            (array)$struct
        );
    }

    /**
     * Get language by id
     *
     * @param mixed $id
     * @return \ezp\Persistence\Content\Language
     * @throws \ezp\Base\Exception\NotFound If language could not be found by $id
     */
    public function load( $id )
    {
        return $this->backend->load( 'Content\\Language', $id );
    }

    /**
     * Get all languages
     *
     * @return \ezp\Persistence\Content\Language[]
     */
    public function loadAll()
    {
        return $this->backend->find( 'Content\\Language', array() );
    }

    /**
     * Delete a language
     *
     * @todo Might throw an exception if the language is still associated with some content / types / (...) ?
     *
     * @param mixed $id
     */
    public function delete( $id )
    {
        $this->backend->delete( 'Content\\Language', $id );
    }
}
?>
