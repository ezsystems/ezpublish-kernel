<?php
/**
 * File containing the UrlAlias Handler
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias;

use eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler as UrlAliasHandlerInterface,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler as LanguageHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor,
    eZ\Publish\SPI\Persistence\Content\UrlAlias,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZ\Publish\Core\Base\Exceptions\ForbiddenException,
    RuntimeException;

/**
 * The UrlAlias Handler provides nice urls management.
 *
 * Its methods operate on a representation of the url alias data structure held
 * inside a storage engine.
 */
class Handler implements UrlAliasHandlerInterface
{
    protected $configuration = array(
        "wordSeparatorName" => "dash",
        "urlAliasNameLimit" => 255,
        "transformation" => "urlalias",
        "transformationGroups" => array(
            "urlalias" => array(
                "commands" => array(
                    //normalize
                    "space_normalize",
                    "hyphen_normalize",
                    "apostrophe_normalize",
                    "doublequote_normalize",
                    "greek_normalize",
                    "endline_search_normalize",
                    "tab_search_normalize",
                    "specialwords_search_normalize",
                    "punctuation_normalize",

                    //transform
                    "apostrophe_to_doublequote",
                    "math_to_ascii",
                    "inverted_to_normal",

                    //decompose
                    "special_decompose",
                    "latin_search_decompose",

                    //transliterate
                    "cyrillic_transliterate_ascii",
                    "greek_transliterate_ascii",
                    "hebrew_transliterate_ascii",
                    "latin1_transliterate_ascii",
                    "latin-exta_transliterate_ascii",

                    //diacritical
                    "cyrillic_diacritical",
                    "greek_diacritical",
                    "latin1_diacritical",
                    "latin-exta_diacritical",
                ),
                "cleanupMethod" => "url_cleanup",
            ),
            "urlalias_iri" => array(
                "commands" => array(),
                "cleanupMethod" => "url_cleanup_iri",
            ),
            "urlalias_compat" => array(
                "commands" => array(
                    //normalize
                    "space_normalize",
                    "hyphen_normalize",
                    "apostrophe_normalize",
                    "doublequote_normalize",
                    "greek_normalize",
                    "endline_search_normalize",
                    "tab_search_normalize",
                    "specialwords_search_normalize",
                    "punctuation_normalize",

                    //transform
                    "apostrophe_to_doublequote",
                    "math_to_ascii",
                    "inverted_to_normal",

                    //decompose
                    "special_decompose",
                    "latin_search_decompose",

                    //transliterate
                    "cyrillic_transliterate_ascii",
                    "greek_transliterate_ascii",
                    "hebrew_transliterate_ascii",
                    "latin1_transliterate_ascii",
                    "latin-exta_transliterate_ascii",

                    //diacritical
                    "cyrillic_diacritical",
                    "greek_diacritical",
                    "latin1_diacritical",
                    "latin-exta_diacritical",

                    //lowercase
                    "ascii_lowercase",
                    "cyrillic_lowercase",
                    "greek_lowercase",
                    "latin1_lowercase",
                    "latin-exta_lowercase",
                    "latin_lowercase",
                ),
                "cleanupMethod" => "url_cleanup_compat",
            ),
        ),
        "reservedNames" => array(
            "class",
            "collaboration",
            "content",
            "error",
            "ezinfo",
            "infocollector",
            "layout",
            "notification",
            "oauth",
            "oauthadmin",
            "package",
            "pdf",
            "role",
            "rss",
            "search",
            "section",
            "settings",
            "setup",
            "shop",
            "state",
            "trigger",
            "url",
            "user",
            "visual",
            "workflow",
            "switchlanguage",
        ),
    );

    /**
     * UrlAlias Gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway $gateway
     */
    protected $gateway;

    /**
     * UrlAlias Mapper
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper $mapper
     */
    protected $mapper;

    /**
     * Caching language handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler
     */
    protected $languageHandler;

    /**
     * Language mask generator
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Transformation processor to normalize URL strings
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor
     */
    protected $transformationProcessor;

    /**
     * Creates a new UrlWildcard Handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway $gateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper $mapper
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler $languageHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor $transformationProcessor
     * @param array $configuration
     */
    public function __construct(
        Gateway $gateway,
        Mapper $mapper,
        LanguageHandler $languageHandler,
        LanguageMaskGenerator $languageMaskGenerator,
        TransformationProcessor $transformationProcessor,
        array $configuration = array()
    )
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
        $this->languageHandler = $languageHandler;
        $this->languageMaskGenerator = $languageMaskGenerator;
        $this->transformationProcessor = $transformationProcessor;
        $this->configuration = $configuration + $this->configuration;
    }

    /**
     * This method creates or updates an urlalias from a new or changed content name in a language
     * (if published). It also can be used to create an alias for a new location of content.
     * On update the old alias is linked to the new one (i.e. a history alias is generated).
     *
     * $alwaysAvailable controls whether the url alias is accessible in all
     * languages.
     *
     * @param mixed $locationId
     * @param mixed $parentLocationId
     * @param string $name the new name computed by the name schema or url alias schema
     * @param string $languageCode
     * @param boolean $alwaysAvailable
     *
     * @return void
     */
    public function publishUrlAliasForLocation( $locationId, $parentLocationId, $name, $languageCode, $alwaysAvailable = false )
    {
        $parentId = $this->gateway->loadLocationEntryIdByAction( "eznode:" . $parentLocationId );

        // Handling special case
        // If last (next to topmost) entry parent is special root entry we handle topmost entry as first level entry
        // That is why we need to reset $parentId to 0
        if ( $parentId != 0 && $this->gateway->isRootEntry( $parentId ) )
        {
            $parentId = 0;
        }

        $uniqueCounter = $this->getUniqueCounterValue( $name, $parentId );
        $name = $this->convertToAlias( $name, "location_" . $locationId );
        $languageId = $this->languageHandler->loadByLanguageCode( $languageCode )->id;
        $action = "eznode:" . $locationId;

        // Exiting the loop with break;
        while ( true )
        {
            $newText = $name . ( $uniqueCounter > 1 ? $uniqueCounter : "" );
            $newTextMD5 = $this->getHash( $newText );
            // Try to load existing entry
            $row = $this->gateway->loadRow( $parentId, $newTextMD5 );

            // If nothing was returned insert new entry
            if ( empty( $row ) )
            {
                // Check for existing active location entry on this level and reuse it's id
                $existingLocationEntry = $this->gateway->loadLocationEntryByParentIdAndAction( $parentId, $action );
                $existingLocationEntryId = !empty( $existingLocationEntry ) ? $existingLocationEntry["id"] : null;
                $data = array(
                    "id" => $existingLocationEntryId,
                    "link" => $existingLocationEntryId,
                    "parent" => $parentId,
                    "action" => $action,
                    // Set mask to language with always available bit
                    "lang_mask" => $languageId | (int)$alwaysAvailable,
                    "text" => $newText,
                    "text_md5" => $newTextMD5,
                );

                $newId = $this->gateway->insertRow( $data );

                break;
            }

            // Row exists, check if it is reusable. There are 3 cases when this is possible:
            // 1. NOP entry
            // 2. existing location or custom alias entry
            // 3. history entry
            if ( $row["action"] == "nop:" || $row["action"] == $action || $row["is_original"] == 0 )
            {
                // Check for existing location entry on this level, if it exists and it's id differs from reusable
                // entry id then reusable entry should be updated with the existing location entry id.
                // Note: existing location entry may be downgraded and relinked later, depending on its language.
                $existingLocationEntry = $this->gateway->loadLocationEntryByParentIdAndAction( $parentId, $action );
                $newId = ( !empty( $existingLocationEntry ) && $existingLocationEntry["id"] != $row["id"] )
                    ? $existingLocationEntry["id"]
                    : $row["id"];
                $data = array(
                    "action" => $action,
                    // In case when NOP row was reused
                    "action_type" => "eznode",
                    // Add language and always available bit to the existing mask with removed always available bit
                    "lang_mask" => ( $row["lang_mask"] & ~1 ) | $languageId | (int)$alwaysAvailable,
                    // Always updating text ensures that letter case changes are stored
                    "text" => $newText,
                    // Set "id" and "link" for case when reusable entry is history
                    "id" => $newId,
                    "link" => $newId,
                    // Entry should be active location entry (original and not alias).
                    // Note: this takes care of taking over custom alias entry for the location on the same level
                    // and with same name and action.
                    // @todo maybe also set redirects = 1 (4.x does not do this)
                    "is_original" => 1,
                    "is_alias" => 0
                );
                $this->gateway->updateRow(
                    $parentId,
                    $newTextMD5,
                    $data
                );

                break;
            }

            // If existing row is not reusable, up the $uniqueCounter and try again
            $uniqueCounter += 1;
        }

        // Cleanup
        /** @var $newId */
        /** @var $newTextMD5 */
        // Note: cleanup does not touch custom and global entries
        $this->gateway->downgrade( $action, $languageId, $parentId, $newTextMD5 );
        $this->gateway->relink( $action, $languageId, $newId, $parentId, $newTextMD5 );
    }

    /**
     * Create a user chosen $alias pointing to $locationId in $languageName.
     *
     * If $languageName is null the $alias is created in the system's default
     * language. $alwaysAvailable makes the alias available in all languages.
     *
     * @param mixed $locationId
     * @param string $path
     * @param array $prioritizedLanguageCodes
     * @param boolean $forwarding
     * @param string $languageCode
     * @param boolean $alwaysAvailable
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function createCustomUrlAlias( $locationId, $path, $forwarding = false, $languageCode = null, $alwaysAvailable = false )
    {
        return $this->createUrlAlias(
            "eznode:" . $locationId,
            $path,
            $forwarding,
            $languageCode,
            $alwaysAvailable
        );
    }

    /**
     * Create a user chosen $alias pointing to a resource in $languageName.
     * This method does not handle location resources - if a user enters a location target
     * the createCustomUrlAlias method has to be used.
     *
     * If $languageName is null the $alias is created in the system's default
     * language. $alwaysAvailable makes the alias available in all languages.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException if the path already exists for the given language
     *
     * @param string $resource
     * @param string $path
     * @param boolean $forwarding
     * @param string $languageCode
     * @param boolean $alwaysAvailable
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function createGlobalUrlAlias( $resource, $path, $forwarding = false, $languageCode = null, $alwaysAvailable = false )
    {
        return $this->createUrlAlias(
            $resource,
            $path,
            $forwarding,
            $languageCode,
            $alwaysAvailable
        );
    }

    /**
     * Internal method for creating global or custom URL alias (these are handled in the same way)
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException if the path already exists for the given language
     *
     * @param string $action
     * @param string $path
     * @param bool $forward
     * @param string|null $languageCode
     * @param bool $alwaysAvailable
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    protected function createUrlAlias( $action, $path, $forward, $languageCode, $alwaysAvailable )
    {
        $pathElements = explode( "/", $path );
        // Pop and store topmost path element, it is handled separately later
        $topElement = array_pop( $pathElements );
        $languageId = $this->languageHandler->loadByLanguageCode( $languageCode )->id;
        $createdPath = array();
        $parentId = 0;

        // Handle all path elements except topmost one
        foreach ( $pathElements as $pathElement )
        {
            $pathElement = $this->convertToAlias( $pathElement, "noname" . count( $createdPath ) + 1 );
            $pathElementMD5 = $this->getHash( $pathElement );
            $row = $this->gateway->loadRow( $parentId, $pathElementMD5 );

            $parentId = empty( $row )
                ? $this->gateway->insertNopRow( $parentId, $pathElement, $pathElementMD5 )
                : $row["link"];

            $createdPath[] = $pathElement;
        }

        // Now handle topmost path element
        $topElement = $this->convertToAlias( $topElement, "noname" . count( $createdPath ) + 1 );

        // If last (next to topmost) entry parent is special root entry we handle topmost entry as first level entry
        // That is why we need to reset $parentId to 0 and empty $createdPath
        if ( $parentId != 0 && $this->gateway->isRootEntry( $parentId ) )
        {
            $parentId = 0;
            $createdPath = array();
        }

        $topElementMD5 = $this->getHash( $topElement );
        // Set common values for two cases below
        $data = array(
            "action" => $action,
            "is_alias" => 1,
            "alias_redirects" => $forward ? 1 : 0,
            "parent" => $parentId,
            "text" => $topElement,
            "text_md5" => $topElementMD5
        );
        // Try to load topmost element
        $row = $this->gateway->loadRow( $parentId, $topElementMD5 );

        // If nothing was returned perform insert
        if ( empty( $row ) )
        {
            $data["lang_mask"] = $languageId | (int)$alwaysAvailable;
            $this->gateway->insertRow( $data );
        }
        // If a entry was returned check if it is reusable
        // There are 3 possible cases:
        // 1. same action and linked to another entry @todo this condition is probably extraneous as linked entry is also a history entry
        // 2. NOP entry
        // 3. history entry
        elseif (
            $row["action"] == $action && $row["id"] != $row["link"]
            || $row["action"] == "nop:"
            || $row["is_original"] == 0
        )
        {
            $data["lang_mask"] = $row["lang_mask"] | $languageId | (int)$alwaysAvailable;
            $this->gateway->updateRow(
                $parentId,
                $topElementMD5,
                $data
            );
        }
        // Path already exists, exit with ForbiddenException
        else
        {
            throw new ForbiddenException( "Path '$path' already exists for the given language" );
        }

        $createdPath[] = $topElement;

        preg_match( "#^([a-zA-Z0-9_]+):(.+)?$#", $action, $matches );
        $data["type"] = $matches[1] === "eznode" ? UrlAlias::LOCATION : UrlALias::RESOURCE;
        $data["destination"] = $matches[2];
        //$data["path_language_data"] = $alwaysAvailable ? array() : array( array( $languageCode ) );
        $data["forward"] = $forward;
        $data["always_available"] = $alwaysAvailable;
        $data["is_original"] = true;
        $data["is_alias"] = true;
        $data["language_codes"][] = $languageCode;

        return $this->mapper->extractUrlAliasFromData( $data );
    }

    /**
     * List of user generated or autogenerated url entries, pointing to $locationId.
     *
     * @param mixed $locationId
     * @param boolean $custom if true the user generated aliases are listed otherwise the autogenerated
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias[]
     */
    public function listURLAliasesForLocation( $locationId, $custom = false )
    {
        $urlAliasListData = $this->gateway->loadUrlAliasListDataByLocationId(
            $locationId,
            $custom
        );

        foreach ( $urlAliasListData as &$urlAliasData )
        {
            $urlAliasData["path_data"] = $this->normalizePathData(
                $this->gateway->loadPathData( $urlAliasData["id"] )
            );
            $languageCodes = array();
            foreach ( $this->languageMaskGenerator->extractLanguageIdsFromMask( $urlAliasData["lang_mask"] ) as $languageId )
            {
                $languageCodes[] = $this->languageHandler->load( $languageId )->languageCode;
            }
            $urlAliasData["language_codes"] = $languageCodes;
            $urlAliasData["always_available"] = (boolean)( $urlAliasData["lang_mask"] & 1 );
            $urlAliasData["forward"] = $custom ? (boolean)$urlAliasData["alias_redirects"] : false;
            $urlAliasData["destination"] = $locationId;
            $urlAliasData["type"] = UrlAlias::LOCATION;
        }

        return $this->mapper->extractUrlAliasListFromData( $urlAliasListData );
    }

    /**
     * @todo document
     *
     * @param string|null $languageCode
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias[]
     */
    public function listGlobalURLAliases( $languageCode = null, $offset = 0, $limit = -1 )
    {
        $urlAliasListData = $this->gateway->loadGlobalUrlAliasListData(
            $languageCode,
            $offset,
            $limit
        );

        foreach ( $urlAliasListData as &$urlAliasData )
        {
            $urlAliasData["path_data"] = $this->normalizePathData(
                $this->gateway->loadPathData( $urlAliasData["id"] )
            );
            $languageCodes = array();
            foreach ( $this->languageMaskGenerator->extractLanguageIdsFromMask( $urlAliasData["lang_mask"] ) as $languageId )
            {
                $languageCodes[] = $this->languageHandler->load( $languageId )->languageCode;
            }
            $urlAliasData["language_codes"] = $languageCodes;
            $urlAliasData["always_available"] = (boolean)( $urlAliasData["lang_mask"] & 1 );
            $urlAliasData["forward"] = (boolean)$urlAliasData["alias_redirects"];
            $resource = explode( ":", $urlAliasData["action"] );
            $urlAliasData["destination"] = $resource[1];
            $urlAliasData["type"] = UrlAlias::RESOURCE;
        }

        return $this->mapper->extractUrlAliasListFromData( $urlAliasListData );
    }

    /**
     * Returns normalized path data loaded by Gateway::loadPathDataByActionList() method.
     *
     * @throws \RuntimeException
     *
     * @param array $actionList
     * @param array $pathData
     *
     * @return array
     */
    protected function normalizeActionListPathData( array $actionList, array $pathData )
    {
        $normalizedPathData = array();
        $pathDataMap = array();
        foreach ( $pathData as $row )
        {
            $action = $row['action'];
            if ( !isset( $pathDataMap[$action] ) )
            {
                $pathDataMap[$action] = array();
            }
            $pathDataMap[$action][] = $row;
        }

        $lastId = false;
        foreach ( $actionList as $level => $action )
        {
            if ( !isset( $pathDataMap[$action] ) )
            {
                throw new \RuntimeException(
                    "The action '{$action}' was not found in the database, can not calculate path."
                );
            }

            $pathElementData = array();
            $auxId = false;
            $parentId = false;
            foreach ( $pathDataMap[$action] as $row )
            {
                if ( $auxId !== false && $auxId !== $row["id"] )
                {
                    // @todo log error
                    throw new \RuntimeException( "Different IDs found for action '$action', the path is corrupted." );
                }

                if ( $parentId !== false && $parentId !== $row["parent"] )
                {
                    // @todo log error
                    throw new \RuntimeException( "Different parent IDs found for action '$action', the path is corrupted." );
                }

                $auxId = $row["id"];
                $parentId = $row["parent"];


                $this->normalizePathDataRow( $pathElementData, $row );
            }

            if ( $level === 0 && $parentId != 0 )
            {
                // @todo log error
                throw new \RuntimeException( "The first path entry with action '$action' is not root entry, the path is corrupted." );
            }

            if ( $lastId !== false && $parentId != $lastId )
            {
                // @todo log error
                throw new \RuntimeException(
                    "The parent ID '$parentId' of element with ID '$auxId' does not point to the last entry which had ID '$lastId', the path is corrupted."
                );
            }

            $lastId = $auxId;
            $normalizedPathData[$level] = $pathElementData;
        }

        return $normalizedPathData;
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
     * @param array $pathElementData
     * @param array $row
     *
     * @return void
     */
    protected function normalizePathDataRow( array &$pathElementData, array $row )
    {
        $languageIds = $this->languageMaskGenerator->extractLanguageIdsFromMask( $row["lang_mask"] );
        $pathElementData["always-available"] = (boolean)( $row["lang_mask"] & 1 );
        if ( !empty( $languageIds ) )
        {
            foreach ( $languageIds as $languageId )
            {
                $pathElementData["translations"][$this->languageHandler->load( $languageId )->languageCode] =
                    $row["text"];
            }
        }
        elseif ( $pathElementData["always-available"] )
        {
            // NOP entry, lang_mask == 1
            $pathElementData["translations"]["always-available"] = $row["text"];
        }
    }

    /**
     * Removes url aliases.
     *
     * Autogenerated aliases are not removed by this method.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\UrlAlias[] $urlAliases
     *
     * @return boolean
     */
    public function removeURLAliases( array $urlAliases )
    {
        foreach ( $urlAliases as $urlAlias )
        {
            if ( $urlAlias->isCustom )
            {
                list( $parentId, $textMD5 ) = explode( "-" , $urlAlias->id );
                if ( !$this->gateway->removeCustomAlias( $parentId, $textMD5 ) )
                {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Looks up a url alias for the given url
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \RuntimeException
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     *
     * @param string $url
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function lookup( $url )
    {
        $urlHashes = array();
        foreach ( explode( "/", $url ) as $level => $text )
        {
            $urlHashes[$level] = $this->getHash( $text );
        }

        $data = $this->gateway->loadUrlAliasData( $urlHashes );
        if ( empty( $data ) )
        {
            throw new NotFoundException( "URLAlias", $url );
        }

        $pathLevels = count( $urlHashes );
        $prefix =  "ezurlalias_ml" . ( $pathLevels - 1 );

        if ( preg_match( "#^([a-zA-Z0-9_]+):(.+)?$#", $data[$prefix . "_action"], $matches ) )
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
                    return $this->getVirtualUrlAlias( $data[$prefix . "_parent"] . "-" . $data[$prefix . "_text_md5"] );
                    break;

                default:
                    // @TODO log message
                    throw new RuntimeException( "Action type '{$actionType}' is unknown" );
            }
        }
        else
        {
            // @TODO log message
            throw new RuntimeException( "Action '{$data[$prefix . "_action"]}' is invalid" );
        }

        $pathLanguageData = array();
        $actionList = array();
        for ( $level = 0; $level < $pathLevels; ++$level )
        {
            $prefix =  "ezurlalias_ml" . $level;
            $actionList[$level] = $data[$prefix . "_action"];
            $pathLevelLanguageData = array(
                "always-available" => (boolean)( $data[$prefix . "_lang_mask"] & 1 ),
                "language-codes" => $this->getLanguageCodesFromMask( $data[$prefix . "_lang_mask"] )
            );
            if ( empty( $pathLevelLanguageData["language-codes"] ) && $pathLevelLanguageData["always-available"] )
            {
                $pathLevelLanguageData["language-codes"][] = "always-available";
            }
            $pathLanguageData[$level] = $pathLevelLanguageData;
        }

        $data["type"] = $type;
        $data["forward"] = $data[$prefix . "_is_alias"] && $data[$prefix . "_alias_redirects"];
        $data["destination"] = $destination;
        $data["language_codes"] = $this->getLanguageCodesFromMask( $data[$prefix . "_lang_mask"] );
        $data["path_language_data"] = $pathLanguageData;
        $data["always_available"] = (bool)( $data[$prefix . "_lang_mask"] & 1 );
        $data["is_original"] = $data[$prefix . "_is_original"];
        $data["is_alias"] = $data[$prefix . "_is_alias"];
        $data["action"] = $data[$prefix . "_action"];
        $data["parent"] = $data[$prefix . "_parent"];
        $data["text_md5"] = $data[$prefix . "_text_md5"];

        if ( $data["type"] === UrlAlias::LOCATION && !$data["is_alias"] && $data["is_original"] )
        {
            $data["path_data"] = $this->normalizeActionListPathData(
                $actionList,
                $this->gateway->loadPathDataByActionList( $actionList )
            );
        }
        else
        {
            $data["path_data"] = $this->normalizePathData(
                $this->gateway->loadPathData( $data[$prefix . "_id"] )
            );
        }

        return $this->mapper->extractUrlAliasFromData( $data );
    }

    protected function getLanguageCodesFromMask( $languageMask )
    {
        $languageCodes = array();

        foreach ( $this->languageMaskGenerator->extractLanguageIdsFromMask( $languageMask ) as $languageId )
        {
            $languageCodes[] = $this->languageHandler->load( $languageId )->languageCode;
        }

        return $languageCodes;
    }

    /**
     * Returns URL alias of type UrlAlias::VIRTUAL.
     *
     * Except for id this alias is the same in all cases.
     *
     * @param $id
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    protected function getVirtualUrlAlias( $id )
    {
        return new UrlAlias(
            array(
                "id" => $id,
                "type" => UrlAlias::VIRTUAL,
                "forward" => true,
                "isCustom" => true,
                "isHistory" => false,
                "alwaysAvailable" => true
            )
        );
    }

    /**
     * Notifies the underlying engine that a location has moved.
     *
     * This method triggers the change of the autogenerated aliases
     *
     * @param mixed $locationId
     * @param mixed $newParentId
     */
    public function locationMoved( $locationId, $newParentId )
    {
        //@todo implement
        //$this->gateway->reparent();
    }

    /**
     * Updates subtree aliases when a location is moved
     */
    protected function updateAliasSubtree()
    {
        //@todo implement
        //$this->updateAliasSubtree();
    }

    /**
     * Notifies the underlying engine that a location has moved.
     *
     * This method triggers the creation of the autogenerated aliases for the copied locations
     *
     * @param mixed $locationId
     * @param mixed $newParentId
     */
    public function locationCopied( $locationId, $newParentId )
    {
        //@todo implement
    }

    /**
     * Notifies the underlying engine that a location was deleted or moved to trash
     *
     * @param $locationId
     */
    public function locationDeleted( $locationId )
    {
        $action = "eznode:" . $locationId;
        $this->removeSubtree(
            $this->gateway->loadLocationEntryIdByAction( $action ),
            $action
        );
    }

    /**
     * @param mixed $parentId
     * @param string $action
     */
    protected function removeSubtree( $parentId, $action )
    {
        $list = $this->gateway->loadLocationAliasDataByParentId( $parentId );

        foreach ( $list as $alias )
        {
            $this->removeSubtree( $alias["id"], $alias["action"] );
        }

        $this->gateway->removeByAction( $action );
    }

    /**
     * Converts the path \a $urlElement into a new alias url which only consists of valid characters
     * in the URL.
     * For non-Unicode setups this means character in the range a-z, numbers and _, for Unicode
     * setups it means all characters except space, &, ;, /, :, =, ?, [, ], (, ), -
     *
     * Invalid characters are converted to -.
     *
     * Example with a non-Unicode setup
     *
     * 'My car' => 'My-car'
     * 'What is this?' => 'What-is-this'
     * 'This & that' => 'This-that'
     * 'myfile.tpl' => 'Myfile-tpl',
     * 'øæå' => 'oeaeaa'
     *
     * @param string $text
     * @param $defaultValue
     *
     * @return string
     */
    protected function convertToAlias( $text, $defaultValue = "_1" )
    {
        if ( strlen( $text ) === 0 )
        {
            $text = $defaultValue;
        }

        return $this->cleanupText(
            $this->transformationProcessor->transform(
                $text,
                $this->configuration["transformationGroups"][$this->configuration["transformation"]]["commands"]
            )
        );
    }

    /**
     * Returns unique counter number that is appended to the path element in order to make it unique
     * against system reserved names and other entries on the same level.
     *
     * Comparison is done only if parent element id is 0 (meaning that entry is at first path element).
     * In a case when reserved name is matched method will return 2.
     * When parent element id is not 0 or when there is no match with reserved names this will return 1,
     * which is default value not appended to name.
     * Note: this is used only when publishing URL aliases, when creating global and custom aliases user
     * is allowed to create first level entries that collide with reserved names. Also, in actual creation
     * of the alias name will be further checked against existing elements under the same parent, using
     * unique counter value determined here as starting unique counter value.
     *
     * @param string $text
     * @param int $parentId
     *
     * @return int
     */
    protected function getUniqueCounterValue( $text, $parentId )
    {
        if ( $parentId === 0 )
        {
            foreach ( $this->configuration["reservedNames"] as $reservedName )
            {
                if ( strcasecmp( $text, $reservedName ) === 0 )
                {
                    return 2;
                }
            }
        }

        return 1;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    protected function getHash( $text )
    {
        return md5( strtolower( $text ) );
    }

    /**
     * @param string $text
     *
     * @return string
     */
    protected function cleanupText( $text )
    {
        switch ( $this->configuration["transformationGroups"][$this->configuration["transformation"]]["cleanupMethod"] )
        {
            case "url_cleanup":
                $sep = $this->getWordSeparator();
                $sepQ = preg_quote( $sep );
                $text = preg_replace(
                    array(
                        "#[^a-zA-Z0-9_!.-]+#",
                        "#^[.]+|[!.]+$#", # Remove dots at beginning/end
                        "#\.\.+#", # Remove double dots
                        "#[{$sepQ}]+#", # Turn multiple separators into one
                        "#^[{$sepQ}]+|[{$sepQ}]+$#" # Strip separator from beginning/end
                    ),
                    array(
                        $sep,
                        $sep,
                        $sep,
                        $sep,
                        ""
                    ),
                    $text
                );
                break;
            case "url_cleanup_iri":
                // With IRI support we keep all characters except some reserved ones,
                // they are space, ampersand, semi-colon, forward slash, colon, equal sign, question mark,
                //          square brackets, parenthesis, plus.
                //
                // Note: Space is turned into a dash to make it easier for people to
                //       paste urls from the system and have the whole url recognized
                //       instead of being broken off
                $sep = $this->getWordSeparator();
                $sepQ = preg_quote( $sep );
                $prepost = " ." . $sepQ;
                if ( $sep != "-" )
                    $prepost .= "-";
                $text = preg_replace(
                    array(
                        "#[ \\\\%\#&;/:=?\[\]()+]+#",
                        "#^[.]+|[!.]+$#", # Remove dots at beginning/end
                        "#\.\.+#", # Remove double dots
                        "#[{$sepQ}]+#", # Turn multiple separators into one
                        "#^[{$prepost}]+|[{$prepost}]+$#"
                    ),
                    array(
                        $sep,
                        $sep,
                        $sep,
                        $sep,
                        ""
                    ),
                    $text
                );
                break;
            case "url_cleanup_compat":
                // Old style of url alias with lowercase only and underscores for separators
                $text = strtolower( $text );
                $text = preg_replace(
                    array(
                        "#[^a-z0-9]+#",
                        "#^_+|_+$#"
                    ),
                    array(
                        "_",
                        ""
                    ),
                    $text
                );
                break;
            default:
                // Nothing
        }

        return $text;
    }

    /**
     * Returns word separator value
     *
     * @return string
     */
    protected function getWordSeparator()
    {
        switch ( $this->configuration["wordSeparatorName"] )
        {
            case "dash": return "-";
            case "underscore": return "_";
            case "space": return " ";
        }

        return "-";
    }
}
