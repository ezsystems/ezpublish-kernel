<?php
/**
 * File containing the UrlAlias Mapper class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias;

use eZ\Publish\SPI\Persistence\Content\UrlAlias,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;

/**
 * UrlAlias Mapper
 */
class Mapper
{
    /**
     * Language mask generator
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Creates a new UrlWildcard Handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator
     */
    public function __construct( LanguageMaskGenerator $languageMaskGenerator )
    {
        $this->languageMaskGenerator = $languageMaskGenerator;
    }

    /**
     * Creates a UrlAlias object from database row data
     *
     * @param $data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function extractUrlAliasFromData( $data )
    {
        $urlAlias = new UrlAlias();

        list( $type, $destination ) = $this->matchTypeAndDestination( $data["action"] );
        $urlAlias->id = $data["parent"] . "-" . $data["text_md5"];
        $urlAlias->pathData = isset( $data["raw_path_data"] )
            ? $this->normalizePathData( $data["raw_path_data"] )
            : null;
        $urlAlias->languageCodes = $this->languageMaskGenerator->extractLanguageCodesFromMask( $data["lang_mask"] );
        $urlAlias->alwaysAvailable = $this->languageMaskGenerator->isAlwaysAvailable( $data["lang_mask"] );
        $urlAlias->isHistory = isset( $data["is_path_history"] ) ? $data["is_path_history"] : !$data["is_original"];
        $urlAlias->isCustom = (boolean)$data["is_alias"];
        $urlAlias->forward = $data["is_alias"] && $data["alias_redirects"];
        $urlAlias->destination = $destination;
        $urlAlias->type = $type;

        return $urlAlias;
    }

    /**
     * Extracts UrlAlias objects from database $rows
     *
     * @param array $rows
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias[]
     */
    public function extractUrlAliasListFromData( array $rows )
    {
        $urlAliases = array();
        foreach ( $rows as $row )
        {
            $urlAliases[] = $this->extractUrlAliasFromData( $row );
        }

        return $urlAliases;
    }

    /**
     *
     *
     * @throws \RuntimeException
     *
     * @param string $action
     *
     * @return array
     */
    protected function matchTypeAndDestination( $action )
    {
        if ( preg_match( "#^([a-zA-Z0-9_]+):(.+)?$#", $action, $matches ) )
        {
            $actionType = $matches[1];
            $actionValue = isset( $matches[2] ) ? $matches[2] : false;

            switch ( $actionType )
            {
                case "eznode":
                    $type = UrlAlias::LOCATION;
                    $destination = $actionValue;
                    break;

                case "module":
                    $type = UrlAlias::RESOURCE;
                    $destination = $actionValue;
                    break;

                case "nop":
                    $type = UrlAlias::VIRTUAL;
                    $destination = null;
                    break;

                default:
                    // @TODO log message
                    throw new \RuntimeException( "Action type '{$actionType}' is unknown" );
            }
        }
        else
        {
            // @TODO log message
            throw new \RuntimeException( "Action '{$action}' is not valid" );
        }

        return array( $type, $destination );
    }

    /**
     *
     *
     * @param array $pathData
     *
     * @return array
     */
    protected function normalizePathData( $pathData )
    {
        $normalizedPathData = array();
        foreach ( $pathData as $level => $rows )
        {
            $pathElementData = array();
            foreach ( $rows as $row )
            {
                $this->normalizePathDataRow( $pathElementData, $row );
            }

            $normalizedPathData[$level] = $pathElementData;
        }

        return $normalizedPathData;
    }

    /**
     *
     *
     * @param array $pathElementData
     * @param array $row
     *
     * @return void
     */
    protected function normalizePathDataRow( array &$pathElementData, array $row )
    {
        $languageCodes = $this->languageMaskGenerator->extractLanguageCodesFromMask( $row["lang_mask"] );
        $pathElementData["always-available"] = $this->languageMaskGenerator->isAlwaysAvailable( $row["lang_mask"] );
        if ( !empty( $languageCodes ) )
        {
            foreach ( $languageCodes as $languageCode )
            {
                $pathElementData["translations"][$languageCode] = $row["text"];
            }
        }
        elseif ( $pathElementData["always-available"] )
        {
            // NOP entry, lang_mask == 1
            $pathElementData["translations"]["always-available"] = $row["text"];
        }
    }
}
