<?php
/**
 * File containing the Language Lookup class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Language;

/**
 * Language Lookup interface
 */
interface Lookup
{
    /**
     * Returns the Language with $id from the cache
     *
     * @param mixed $id
     * @return \ezp\Persistence\Content\Language
     * @throws \ezp\Base\Exception\NotFound
     *         if the Language could not be found
     */
    public function getById( $id );

    /**
     * Returns the Language with $locale from the cache
     *
     * @param string $locale
     * @return \ezp\Persistence\Content\Language
     * @throws \ezp\Base\Exception\NotFound
     *         if the Language could not be found
     */
    public function getByLocale( $locale );
}
