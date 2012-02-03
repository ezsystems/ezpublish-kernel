<?php
/**
 * File containing the Language Handler interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\SPI\Persistence\Content\Language,
    eZ\Publish\SPI\Persistence\Content\Language\CreateStruct;

/**
 * Language Handler interface
 */
interface Handler
{
    /**
     * Create a new language
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language\CreateStruct $struct
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    public function create( CreateStruct $struct );

    /**
     * Update language
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language $struct
     */
    public function update( Language $struct );

    /**
     * Get language by id
     *
     * @param mixed $id
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     * @throws \ezp\Base\Exception\NotFound If language could not be found by $id
     */
    public function load( $id );

    /**
     * Get all languages
     *
     * Return list of languages where key of hash is language code.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    public function loadAll();

    /**
     * Delete a language
     *
     * @todo Might throw an exception if the language is still associated with some content / types / (...) ?
     *
     * @param mixed $id
     */
    public function delete( $id );
}
?>
