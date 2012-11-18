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
    eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor,
    eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway as LocationGateway,
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
     * Gateway for handling location data
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected $locationGateway;

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
     * Transformation processor to normalize URL strings
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor
     */
    protected $transformationProcessor;

    /**
     * Creates a new UrlAlias Handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway $gateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper $mapper
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway $locationGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler $languageHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor $transformationProcessor
     * @param array $configuration
     */
    public function __construct(
        Gateway $gateway,
        Mapper $mapper,
        LocationGateway $locationGateway,
        LanguageHandler $languageHandler,
        TransformationProcessor $transformationProcessor,
        array $configuration = array()
    )
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
        $this->locationGateway = $locationGateway;
        $this->languageHandler = $languageHandler;
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
        $parentId = $this->getRealAliasId( $parentLocationId );
        $uniqueCounter = $this->getUniqueCounterValue( $name, $parentId );
        $name = $this->convertToAlias( $name, "location_" . $locationId );
        $languageId = $this->languageHandler->loadByLanguageCode( $languageCode )->id;
        $languageMask = $languageId | (int)$alwaysAvailable;
        $action = "eznode:" . $locationId;
        $cleanup = false;

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
                $existingLocationEntry = $this->gateway->loadAutogeneratedEntry( $action, $parentId );
                if ( !empty( $existingLocationEntry ) )
                {
                    $cleanup = true;
                    $newId = $existingLocationEntry["id"];
                }
                else
                {
                    $newId = null;
                }

                $newId = $this->gateway->insertRow(
                    array(
                        "id" => $newId,
                        "link" => $newId,
                        "parent" => $parentId,
                        "action" => $action,
                        "lang_mask" => $languageMask,
                        "text" => $newText,
                        "text_md5" => $newTextMD5,
                    )
                );

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
                $existingLocationEntry = $this->gateway->loadAutogeneratedEntry( $action, $parentId );
                $newId = $row["id"];
                if ( !empty( $existingLocationEntry ) )
                {
                    if ( $existingLocationEntry["id"] != $row["id"] )
                    {
                        $cleanup = true;
                        $newId = $existingLocationEntry["id"];
                    }
                    else
                    {
                        // If we are reusing existing location entry merge existing language mask
                        $languageMask |= ( $row["lang_mask"] & ~1 );
                    }
                }
                $this->gateway->updateRow(
                    $parentId,
                    $newTextMD5,
                    array(
                        "action" => $action,
                        // In case when NOP row was reused
                        "action_type" => "eznode",
                        "lang_mask" => $languageMask,
                        // Updating text ensures that letter case changes are stored
                        "text" => $newText,
                        // Set "id" and "link" for case when reusable entry is history
                        "id" => $newId,
                        "link" => $newId,
                        // Entry should be active location entry (original and not alias).
                        // Note: this takes care of taking over custom alias entry for the location on the same level
                        // and with same name and action.
                        "alias_redirects" => 1,
                        "is_original" => 1,
                        "is_alias" => 0,
                    )
                );

                break;
            }

            // If existing row is not reusable, increment $uniqueCounter and try again
            $uniqueCounter += 1;
        }

        /** @var $newId */
        /** @var $newTextMD5 */
        // Note: cleanup does not touch custom and global entries
        if ( $cleanup )
        {
            $this->gateway->cleanupAfterPublish( $action, $languageId, $newId, $parentId, $newTextMD5 );
        }
    }

    /**
     * Create a user chosen $alias pointing to $locationId in $languageCode.
     *
     * If $languageCode is null the $alias is created in the system's default
     * language. $alwaysAvailable makes the alias available in all languages.
     *
     * @param mixed $locationId
     * @param string $path
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
     * Create a user chosen $alias pointing to a resource in $languageCode.
     * This method does not handle location resources - if a user enters a location target
     * the createCustomUrlAlias method has to be used.
     *
     * If $languageCode is null the $alias is created in the system's default
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
     * @throws \eZ\Publish\Core\Base\Exceptions\ForbiddenException if the path already exists for the given language
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
        $topElement = array_pop( $pathElements );
        $languageId = $this->languageHandler->loadByLanguageCode( $languageCode )->id;
        $parentId = 0;

        // Handle all path elements except topmost one
        $isPathNew = false;
        foreach ( $pathElements as $level => $pathElement )
        {
            $pathElement = $this->convertToAlias( $pathElement, "noname" . $level + 1 );
            $pathElementMD5 = $this->getHash( $pathElement );
            if ( !$isPathNew )
            {
                $row = $this->gateway->loadRow( $parentId, $pathElementMD5 );
                if ( empty( $row ) )
                {
                    $isPathNew = true;
                }
                else
                {
                    $parentId = $row["link"];
                }
            }

            if ( $isPathNew )
            {
                $parentId = $this->insertNopEntry( $parentId, $pathElement, $pathElementMD5 );
            }
        }

        // Handle topmost path element
        $topElement = $this->convertToAlias( $topElement, "noname" . count( $pathElements ) + 1 );

        // If last (next to topmost) entry parent is special root entry we handle topmost entry as first level entry
        // That is why we need to reset $parentId to 0 and empty $createdPath
        if ( $parentId != 0 && $this->gateway->isRootEntry( $parentId ) )
        {
            $parentId = 0;
        }

        $topElementMD5 = $this->getHash( $topElement );
        // Set common values for two cases below
        $data = array(
            "action" => $action,
            "is_alias" => 1,
            "alias_redirects" => $forward ? 1 : 0,
            "parent" => $parentId,
            "text" => $topElement,
            "text_md5" => $topElementMD5,
            "is_original" => 1
        );
        // Try to load topmost element
        if ( !$isPathNew )
        {
            $row = $this->gateway->loadRow( $parentId, $topElementMD5 );
        }

        // If nothing was returned perform insert
        if ( $isPathNew || empty( $row ) )
        {
            $data["lang_mask"] = $languageId | (int)$alwaysAvailable;
            $this->gateway->insertRow( $data );
        }
        // Row exists, check if it is reusable. There are 2 cases when this is possible:
        // 1. NOP entry
        // 2. history entry
        elseif ( $row["action"] == "nop:" || $row["is_original"] == 0 )
        {
            $data["lang_mask"] = $languageId | (int)$alwaysAvailable;
            // If history is reused move link to id
            $data["link"] = $row["id"];
            $this->gateway->updateRow(
                $parentId,
                $topElementMD5,
                $data
            );
        }
        else
        {
            throw new ForbiddenException( "Path '$path' already exists for the given language" );
        }

        return $this->mapper->extractUrlAliasFromData( $data );
    }

    /**
     * Convenience method for inserting nop type row.
     *
     * @param mixed $parentId
     * @param string $text
     * @param string $textMD5
     *
     * @return mixed
     */
    protected function insertNopEntry( $parentId, $text, $textMD5 )
    {
        return $this->gateway->insertRow(
            array(
                "lang_mask" => 1,
                "action" => "nop:",
                "parent" => $parentId,
                "text" => $text,
                "text_md5" => $textMD5
            )
        );
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
        $data = $this->gateway->loadLocationEntries( $locationId, $custom );
        foreach ( $data as &$entry )
        {
            $entry["raw_path_data"] = $this->gateway->loadPathData( $entry["id"] );
        }

        return $this->mapper->extractUrlAliasListFromData( $data );
    }

    /**
     * List global aliases.
     *
     * @param string|null $languageCode
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias[]
     */
    public function listGlobalURLAliases( $languageCode = null, $offset = 0, $limit = -1 )
    {
        $data = $this->gateway->listGlobalEntries( $languageCode, $offset, $limit );
        foreach ( $data as &$entry )
        {
            $entry["raw_path_data"] = $this->gateway->loadPathData( $entry["id"] );
        }

        return $this->mapper->extractUrlAliasListFromData( $data );
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

        $pathDepth = count( $urlHashes );
        $hierarchyData = array();
        $isPathHistory = false;
        for ( $level = 0; $level < $pathDepth; ++$level )
        {
            $prefix = $level === $pathDepth - 1 ? "" : "ezurlalias_ml" . $level . "_";
            $isPathHistory = $isPathHistory ?: ( $data[$prefix . "link"] != $data[$prefix . "id"] );
            $hierarchyData[$level] = array(
                "id" => $data[$prefix . "id"],
                "parent" => $data[$prefix . "parent"],
                "action" => $data[$prefix . "action"]
            );
        }

        $data["is_path_history"] = $isPathHistory;
        $data["raw_path_data"] = ( $data["action_type"] == "eznode" && !$data["is_alias"] )
            ? $this->gateway->loadPathDataByHierarchy( $hierarchyData )
            : $this->gateway->loadPathData( $data["id"] );

        return $this->mapper->extractUrlAliasFromData( $data );
    }

    /**
     * Loads URL alias by given $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @param string $id
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function loadUrlAlias( $id )
    {
        list( $parentId, $textMD5 ) = explode( "-", $id );
        $data = $this->gateway->loadRow( $parentId, $textMD5 );

        if ( empty( $data ) )
        {
            throw new NotFoundException( "URLAlias", $id );
        }

        $data["raw_path_data"] = $this->gateway->loadPathData( $data["id"] );

        return $this->mapper->extractUrlAliasFromData( $data );
    }

    /**
     * Notifies the underlying engine that a location has moved.
     *
     * This method triggers the change of the autogenerated aliases.
     *
     * @param mixed $locationId
     * @param mixed $oldParentId
     * @param mixed $newParentId
     *
     * @return void
     */
    public function locationMoved( $locationId, $oldParentId, $newParentId )
    {
        // @todo optimize: $newLocationAliasId is already available in self::publishUrlAliasForLocation() as $newId
        $newParentLocationAliasId = $this->getRealAliasId( $newParentId );
        $newLocationAlias = $this->gateway->loadAutogeneratedEntry(
            "eznode:" . $locationId,
            $newParentLocationAliasId
        );

        $oldParentLocationAliasId = $this->getRealAliasId( $oldParentId );
        $oldLocationAlias = $this->gateway->loadAutogeneratedEntry(
            "eznode:" . $locationId,
            $oldParentLocationAliasId
        );

        // Historize alias for old location
        $this->gateway->historizeId( $oldLocationAlias["id"], $newLocationAlias["id"] );
        // Reparent subtree of old location to new location
        $this->gateway->reparent( $oldLocationAlias["id"], $newLocationAlias["id"] );
    }

    /**
     * Notifies the underlying engine that a location has moved.
     *
     * This method triggers the creation of the autogenerated aliases for the copied locations
     *
     * @param mixed $locationId
     * @param mixed $oldParentId
     * @param mixed $newParentId
     *
     * @return void
     */
    public function locationCopied( $locationId, $newLocationId, $newParentId )
    {
        $newParentAliasId = $this->getRealAliasId( $newLocationId );
        $oldParentAliasId = $this->getRealAliasId( $locationId );

        $actionMap = $this->getCopiedLocationsMap( $locationId, $newLocationId );

        $this->copySubtree(
            $actionMap,
            $oldParentAliasId,
            $newParentAliasId,
            $locationId
        );
    }

    /**
     * Returns possibly corrected alias id for given $locationId.
     *
     * First level entries must have parent id set to 0 instead of their parent location alias id.
     * There are two cases when alias id needs to be corrected:
     * 1) location is special location without URL alias (location with id=1 in standard installation)
     * 2) location is site root location, having special root entry in the ezurlalias_ml table (location with id=2
     *    in standard installation)
     *
     * @param mixed $locationId
     *
     * @return int
     */
    protected function getRealAliasId( $locationId )
    {
        $data = $this->gateway->loadAutogeneratedEntry( "eznode:" . $locationId );

        if ( empty( $data ) || $data["id"] != 0 && $this->gateway->isRootEntry( $data["id"] ) )
        {
            $id = 0;
        }
        else
        {
            $id = $data["id"];
        }

        return $id;
    }

    /**
     * Recursively copies aliases from old parent under new parent.
     *
     * @param array $actionMap
     * @param mixed $oldParentAliasId
     * @param mixed $newParentAliasId
     *
     * @return void
     */
    protected function copySubtree( $actionMap, $oldParentAliasId, $newParentAliasId )
    {
        $rows = $this->gateway->loadAutogeneratedEntries( $oldParentAliasId );
        $newIdsMap = array();
        foreach ( $rows as $row )
        {
            $oldParentAliasId = $row["id"];

            // Ensure that same action entries remain grouped by the same id
            if ( !isset( $newIdsMap[$oldParentAliasId] ) )
            {
                $newIdsMap[$oldParentAliasId] = $this->gateway->getNextId();

         }


            $row["action"] = $actionMap[$row["action"]];
            $row["parent"] = $newParentAliasId;
            $row["id"] = $row["link"] = $newIdsMap[$oldParentAliasId];
            $this->gateway->insertRow( $row );

            $this->copySubtree(
                $actionMap,
                $oldParentAliasId,
                $row["id"]
            );
        }
    }

    /**
     *
     *
     * @param mixed $oldParentId
     * @param mixed $newParentId
     *
     * @return array
     */
    protected function getCopiedLocationsMap( $oldParentId, $newParentId )
    {
        $originalLocations = $this->locationGateway->getSubtreeContent( $oldParentId );
        $copiedLocations = $this->locationGateway->getSubtreeContent( $newParentId );

        $map = array();
        foreach ( $originalLocations as $index => $originalLocation )
        {
            $map["eznode:" . $originalLocation["node_id"]] = "eznode:" . $copiedLocations[$index]["node_id"];
        }

        return $map;
    }

    /**
     * Notifies the underlying engine that a location was deleted or moved to trash
     *
     * @param $locationId
     */
    public function locationDeleted( $locationId )
    {
        $action = "eznode:" . $locationId;
        $entry = $this->gateway->loadAutogeneratedEntry( $action );

        $this->removeSubtree( $entry["id"], $action );
    }

    /**
     * Recursively removes aliases by given parent id and action
     *
     * @param mixed $parentId
     * @param string $action
     *
     * @return void
     */
    protected function removeSubtree( $parentId, $action )
    {
        $entries = $this->gateway->loadAutogeneratedEntries( $parentId, true );

        foreach ( $entries as $entry )
        {
            $this->removeSubtree( $entry["id"], $entry["action"] );
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
     * Cleans up
     *
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
