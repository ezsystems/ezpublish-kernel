<?php
/**
 * File containing the UrlAlias Handler
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias;

use eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler as BaseUrlAliasHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler as LanguageHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator,
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
class Handler implements BaseUrlAliasHandler
{
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
     * List of system reserved URL alias names
     *
     * @var array
     */
    protected $reservedNames = array();

    /**
     * Creates a new UrlWildcard Handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway $gateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper $mapper
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler $languageHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator
     */
    public function __construct(
        Gateway $gateway,
        Mapper $mapper,
        LanguageHandler $languageHandler,
        LanguageMaskGenerator $languageMaskGenerator )
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
        $this->languageHandler = $languageHandler;
        $this->languageMaskGenerator = $languageMaskGenerator;
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
        //$data["path_language_codes"] = $alwaysAvailable ? array() : array( array( $languageCode ) );
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
     *
     *
     * @param array $pathData
     *
     * @return array
     */
    protected function normalizePathData( $pathData )
    {
        $normalizedPathData = array();
        foreach ( $pathData as $level => $rawPathElementData )
        {
            $pathElementData = array();
            foreach ( $rawPathElementData as $rawPathElementEntry )
            {
                $languageIds = $this->languageMaskGenerator->extractLanguageIdsFromMask(
                    $rawPathElementEntry["lang_mask"]
                );
                $pathElementData["always-available"] = (boolean)( $rawPathElementEntry["lang_mask"] & 1 );
                if ( !empty( $languageIds ) )
                {
                    foreach ( $languageIds as $languageId )
                    {
                        $pathElementData["translations"][$this->languageHandler->load( $languageId )->languageCode] =
                            $rawPathElementEntry["text"];
                    }
                }
                elseif ( $pathElementData["always-available"] )
                {
                    // NOP entry, lang_mask == 1
                    $pathElementData["translations"]["always-available"] = $rawPathElementEntry["text"];
                }
            }

            $normalizedPathData[$level] = $pathElementData;
        }

        return $normalizedPathData;
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
        for ( $level = 0; $level < $pathLevels; ++$level )
        {
            $prefix =  "ezurlalias_ml" . $level;
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
        $data["path_data"] = $this->normalizePathData( $this->gateway->loadPathData( $data[$prefix . "_id"] ) );
        $data["path_language_codes"] = $pathLanguageData;
        $data["always_available"] = (bool)( $data[$prefix . "_lang_mask"] & 1 );
        $data["is_original"] = $data[$prefix . "_is_original"];
        $data["is_alias"] = $data[$prefix . "_is_alias"];
        $data["action"] = $data[$prefix . "_action"];
        $data["parent"] = $data[$prefix . "_parent"];
        $data["text_md5"] = $data[$prefix . "_text_md5"];

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
        //$this->updateAliasSubtree();
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
        //@todo implement
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
     * @param string $urlElement
     * @param $defaultValue
     *
     * @return string
     *
     * @todo: implement eZCharTransform
     */
    protected function convertToAlias( $urlElement, $defaultValue = "_1" )
    {
        if ( strlen( $urlElement ) === 0 )
        {
            $urlElement = $defaultValue;
        }

        /*
        $trans = eZCharTransform::instance();

        $ini = eZINI::instance();
        $group = $ini->variable( 'URLTranslator', 'TransformationGroup' );

        $urlElement = $trans->transformByGroup( $urlElement, $group );

        return $trans->transformByGroup(
            strlen( $urlElement ) === 0
                ? $defaultValue
                : $urlElement,
            $group
        );
        */

        return $urlElement;
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
     * @param string $name
     * @param int $parentId
     *
     * @return int
     */
    protected function getUniqueCounterValue( $name, $parentId )
    {
        if ( $parentId === 0 )
        {
            foreach ( $this->reservedNames as $reservedName )
            {
                if ( strcasecmp( $name, $reservedName ) )
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
     *
     * @todo use utility method to downcase
     */
    protected function getHash( $text )
    {
        return md5( strtolower( $text ) );
    }
}
