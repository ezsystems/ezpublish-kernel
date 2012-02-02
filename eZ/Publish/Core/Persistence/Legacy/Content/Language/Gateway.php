<?php
/**
 * File containing the Language Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Language;
use eZ\Publish\SPI\Persistence\Content\Language;

/**
 * Language Handler
 */
abstract class Gateway
{
    /**
     * Inserts the given $language
     *
     * @param Language $language
     * @return int ID of the new language
     */
    abstract public function insertLanguage( Language $language );

    /**
     * Updates the data of the given $language
     *
     * @param Language $language
     * @return void
     */
    abstract public function updateLanguage( Language $language );

    /**
     * Loads data for the Language with $id
     *
     * @param int $id
     * @return string[][]
     */
    abstract public function loadLanguageData( $id );

    /**
     * Loads the data for all languages
     *
     * @return string[][]
     */
    abstract public function loadAllLanguagesData();

    /**
     * Deletes the language with $id
     *
     * @param int $id
     * @return void
     */
    abstract public function deleteLanguage( $id );
}
