<?php
/**
 * File containing the Language Mapper class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Language;
use ezp\Persistence\Content\Language,
    ezp\Persistence\Content\Language\CreateStruct;

/**
 * Language Mapper
 */
class Mapper
{
    /**
     * Creates a Language from $struct
     *
     * @param \ezp\Persistence\Content\Language\CreateStruct $struct
     * @return \ezp\Persistence\Content\Language
     */
    public function createLanguageFromCreateStruct( CreateStruct $struct )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }

    /**
     * Extracts Language objects from $rows
     *
     * @param array $rows
     * @return \ezp\Persistence\Content\Language[]
     */
    public function extractLanguagesFromRows( array $rows )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }
}
