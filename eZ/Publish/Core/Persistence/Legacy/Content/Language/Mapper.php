<?php
/**
 * File containing the Language Mapper class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Language;

use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\SPI\Persistence\Content\Language\CreateStruct;

/**
 * Language Mapper
 */
class Mapper
{
    /**
     * Creates a Language from $struct
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language\CreateStruct $struct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    public function createLanguageFromCreateStruct( CreateStruct $struct )
    {
        $language = new Language();

        $language->languageCode = $struct->languageCode;
        $language->name = $struct->name;
        $language->isEnabled = $struct->isEnabled;

        return $language;
    }

    /**
     * Extracts Language objects from $rows
     *
     * @param array $rows
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    public function extractLanguagesFromRows( array $rows )
    {
        $languages = array();

        foreach ( $rows as $row )
        {
            $language = new Language();

            $language->id = (int)$row['id'];
            $language->languageCode = $row['locale'];
            $language->name = $row['name'];
            $language->isEnabled = !( (int)$row['disabled'] );

            $languages[$row['locale']] = $language;
        }

        return $languages;
    }
}
