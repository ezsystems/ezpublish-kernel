<?php
/**
 * File containing the UrlAlias Mapper class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias;

use eZ\Publish\SPI\Persistence\Content\UrlAlias;

/**
 * UrlAlias Mapper
 */
class Mapper
{
    /**
     * Creates a UrlAlias object from database row data
     *
     * @param $data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function extractUrlAliasFromRow( $data )
    {
        $urlAlias = new UrlAlias();

        $urlAlias->id = $data["parent"] . "-" . $data["text_md5"];
        $urlAlias->languageCodes = $data["language_codes"];
        $urlAlias->alwaysAvailable = $data["always_available"];
        $urlAlias->isHistory = !$data["is_original"];
        $urlAlias->isCustom = (boolean)$data["is_alias"];
        $urlAlias->path = $data["path"];
        $urlAlias->forward = $data["forward"];
        $urlAlias->destination = $data["destination"];
        $urlAlias->type = $data["type"];

        return $urlAlias;
    }

    /**
     * Extracts UrlAlias objects from database $rows
     *
     * @param array $rows
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias[]
     */
    public function extractUrlAliasListFromRows( array $rows )
    {
        $urlAliases = array();
        foreach ( $rows as $row )
            $urlAliases[] = $this->extractUrlAliasFromRow( $row );

        return $urlAliases;
    }

    public function extractPathFromRows()
    {

    }

    /**
     *
     *
     * @param array $rows
     * @param \eZ\Publish\SPI\Persistence\Content\Language[] $prioritizedLanguages
     *
     * @return array
     */
    protected function choosePrioritizedRow( array $rows, $prioritizedLanguages )
    {
        $result = false;
        $score = 0;
        foreach ( $rows as $row )
        {
            if ( $result )
            {
                $newScore = $this->languageScore( $row['lang_mask'], $prioritizedLanguages );
                if ( $newScore > $score )
                {
                    $result = $row;
                    $score = $newScore;
                }
            }
            else
            {
                $result = $row;
                $score = $this->languageScore( $row['lang_mask'], $prioritizedLanguages );
            }
        }

        // If score is still 0, this means that the objects languages don't
        // match the INI settings, and these should be fix according to the doc.
        if ( $score == 0 )
        {
            // @todo: notice
            // None of the available languages are prioritized in the SiteLanguageList setting.
            // An arbitrary language will be used.
        }

        return $result;
    }

    /**
     * @param $mask
     * @param \eZ\Publish\SPI\Persistence\Content\Language[] $prioritizedLanguages
     *
     * @return int|mixed
     */
    protected function languageScore( $mask, $prioritizedLanguages )
    {
        $scores = array();
        $score = 1;
        $mask   = (int)$mask;
        krsort( $prioritizedLanguages );

        foreach ( $prioritizedLanguages as $language )
        {
            $id = (int)$language->id;
            if ( $id & $mask )
            {
                $scores[] = $score;
            }
            ++$score;
        }

        if ( count( $scores ) > 0 )
        {
            return max( $scores );
        }

        return 0;
    }
}
