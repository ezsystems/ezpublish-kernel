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
    eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway as LocationGateway,
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
     * Location handler.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    public $locationGateway;

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
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway $locationGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler $languageHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator
     */
    public function __construct(
        Gateway $gateway,
        Mapper $mapper,
        LocationGateway $locationGateway,
        LanguageHandler $languageHandler,
        LanguageMaskGenerator $languageMaskGenerator )
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
        $this->locationGateway = $locationGateway;
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
     * @param string $name the new name computed by the name schema or url alias schema
     * @param string $languageCode
     * @param boolean $alwaysAvailable
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     *
     * @todo maybe pass array of prioritized language codes in order to return correct language set
     */
    public function publishUrlAliasForLocation( $locationId, $name, $languageCode, $alwaysAvailable = false )
    {
        $parentLocationData = $this->locationGateway->getBasicNodeData( $locationId );
        $parentAction = "eznode:" . $parentLocationData["parent_node_id"];
        $parentId = $this->gateway->loadLocationEntryIdByAction( $parentAction );

        // Handling special case
        // If last (next to topmost) entry parent is special root entry we handle topmost entry as first level entry
        // That is why we need to reset $parentId to 0 and empty $createdPath
        if ( $parentId != 0 && $this->gateway->isRootEntry( $parentId ) )
        {
            $parentId = 0;
        }

        $uniqueCounter = $this->getUniqueCounterValue( $name, $parentId );
        $name = $this->convertToAlias( $name, "location_" . $locationId );// @todo here be URL transformation
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
                // @todo detect republishing of the same alias and bail out
                if ( false )
                {
                    $isRepublish = true;
                }

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
                    // Entry should be active location entry (original and not alias). @todo maybe also set redirects = 1 (4.x does not do this)
                    // Note: this takes care of taking over custom alias entry on the same level and with same text.
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

        $data["parent"] = $parentId;
        $data["text_md5"] = $newTextMD5;
        $data["type"] = UrlAlias::LOCATION;
        $data["path"] = $this->gateway->getPath( $newId, array( $languageCode ) );
        $data["forward"] = false;
        $data["destination"] = $locationId;
        $data["always_available"] = $alwaysAvailable;
        $data["is_original"] = true;
        $data["is_alias"] = false;
        foreach ( $this->languageMaskGenerator->extractLanguageIdsFromMask( $data["lang_mask"] ) as $languageId )
        {
            $data["language_codes"][] = $this->languageHandler->load( $languageId )->languageCode;
        }

        return $this->mapper->extractUrlAliasFromData( $data );
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
    public function createCustomUrlAlias( $locationId, $path, array $prioritizedLanguageCodes, $forwarding = false, $languageCode = null, $alwaysAvailable = false )
    {
        return $this->createUrlAlias(
            "eznode:" . $locationId,
            $path,
            $forwarding,
            $languageCode,
            $alwaysAvailable,
            $prioritizedLanguageCodes
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
     * @param array $prioritizedLanguageCodes
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    protected function createUrlAlias( $action, $path, $forward, $languageCode, $alwaysAvailable, array $prioritizedLanguageCodes = null )
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
            $pathElement = $this->convertToAlias( $pathElement, "noname" . count( $createdPath ) + 1 );// @todo transformation here
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
        // 1. @todo document
        // 2. NOP entry
        // 3. history entry
        elseif (
            ( $row["action"] == $action && $row["id"] == $row["link"] )
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
            throw new ForbiddenException( "Path '\$path' already exists for the given language" );
        }

        $createdPath[] = $topElement;

        preg_match( "#^([a-zA-Z0-9_]+):(.+)?$#", $action, $matches );
        $data["type"] = $matches[1] === "eznode" ? UrlAlias::VIRTUAL : UrlALias::RESOURCE;
        $data["path"] = implode( "/", $createdPath );
        $data["forward"] = $forward;
        $data["destination"] = $data["type"] === UrlAlias::RESOURCE || !$forward
            ? $matches[2]
            : $this->gateway->getPath(
                $this->gateway->loadLocationEntryIdByAction( $action ),
                $prioritizedLanguageCodes
            );
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
     * @param array $prioritizedLanguageCodes
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias[]
     */
    public function listURLAliasesForLocation( $locationId, $custom = false, array $prioritizedLanguageCodes )
    {
        return $this->mapper->extractUrlAliasListFromData(
            $this->gateway->loadUrlAliasListDataByLocationId(
                $locationId,
                $custom,
                $prioritizedLanguageCodes
            )
        );
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

    }

    /**
     * Looks up a url alias for the given url
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \RuntimeException
     *
     * @param $url
     * @param string[] $prioritizedLanguageCodes
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function lookup( $url, array $prioritizedLanguageCodes )
    {
        $urlElements = explode( "/", $url );
        $urlElementsCount = count( $urlElements );
        $data = $this->gateway->loadBasicUrlAliasData( $urlElements, $prioritizedLanguageCodes );

        if ( !empty( $data ) )
        {
            $destination = null;
            $forwardToAction = false;
            $isFallback = false;
            $type = UrlAlias::LOCATION;
            $forward = false;
            $caseVerifiedPath = array();
            $actions = array();

            for ( $i = 0; $i < $urlElementsCount; ++$i )
            {
                $caseVerifiedPath[] = $data["ezurlalias_ml{$i}_text"];
                $actions[] = $data["ezurlalias_ml{$i}_action"];
            }
            $caseVerifiedPath = implode( "/", $caseVerifiedPath );

            // Determine forward flag
            if ( $data["link"] != $data["id"] )
            {
                // If last URL element entry does not link to its "id", redirection to linked entry action
                // should be performed
                $forward = true;
            }
            elseif ( $data["is_alias"] )
            {
                if ( preg_match( "#^module:.+$#", $data["action"] ) )
                {
                    $type = UrlAlias::RESOURCE;
                }
                else
                {
                    $type = UrlAlias::VIRTUAL;
                }
                if ( $data["alias_redirects"] )
                {
                    // If the entry is an alias and we have an action we redirect to the original
                    // URL of that action
                    $forward = $forwardToAction = true;
                }
            }

            // Determining destination
            // First block is executed if $forward is true (alias redirects or link points to different entry) and
            // alias type is not resource or if $forward is false and path is not case verified.
            // Note: in latter case we redirect to valid URL which can differ from what was compared against, so it
            // is necessary to get case-correct path (this can actually differ from what the $url was compared against)
            if (
                $data["action"] !== "nop:"
                && (
                    ( $forward && $type !== UrlAlias::RESOURCE )
                    || ( !$forward && strcmp( $url, $caseVerifiedPath ) !== 0 )
                )
            )
            {
                // Set $forward to true here for case when path was not case verified
                $forward = true;
                // When redirecting type can be UrlAlias::VIRTUAL or UrlAlias::RESOURCE
                // In latter case path was not case verified and redirect is done to the case-correct path
                //$type = UrlAlias::VIRTUAL;

                $destination = $this->gateway->getPath(
                    $forwardToAction
                        ? $this->gateway->loadLocationEntryIdByAction( $data["action"] )
                        : $data["link"],
                    $prioritizedLanguageCodes
                );

                if ( !isset( $destination ) )
                {
                    // @TODO log something
                    throw new RuntimeException( "Path for URL '{$url}' is broken" );
                }
            }
            else
            {
                // In all other cases just translate action to destination
                // Note: if $forward is true this will be executed only for resource (global) aliases,
                // in all other cases $forward will be false
                if ( preg_match( "#^([a-zA-Z0-9_]+):(.+)?$#", $data["action"], $matches ) )
                {
                    $actionType = $matches[1];
                    $actionValue = isset( $matches[2] ) ? $matches[2] : false;

                    switch ( $actionType )
                    {
                        case "eznode":
                            $destination = is_numeric( $actionValue ) ? $actionValue : false;
                            break;

                        case "module":
                            $destination = $actionValue;
                            break;

                        // Default for NOP action is displaying the root location "/"
                        // It is also needed to take a note of this, since a wildcard pattern might exist for that path
                        // which can not be determined here
                        case "nop":
                            $forward = true;
                            $type = UrlAlias::VIRTUAL;
                            $isFallback = true;
                            $data["is_alias"] = "1";
                            $data["is_original"] = "1";
                            $destination = "/";
                            break;
                    }
                }

                if ( !isset( $destination ) )
                {
                    // @TODO log something
                    throw new RuntimeException( "Action '{$data["action"]}' is invalid" );
                }
            }
        }

        if ( !isset( $destination ) )
        {
            throw new NotFoundException( "URLAlias", $url );
        }

        /** @var $type */
        $data["type"] = $type;
        //$data["language_codes"] = array();
        /** @var $actions */
        if ( $type === UrlAlias::LOCATION )
        {
            // If UrlAlias is of type UrlAlias::LOCATION additional query is needed to determine the languages for it.
            // This is so because it is possible that Content UrlAlias is pointing to is available in a different
            // language than that of the alias that was requested. For example if content has two translations,
            // Croatian and English with respective aliases /jedan and /one, and /jedan was requested on a site where
            // most prioritized language is English (with Croatian also in the list), self::loadBasicUrlAliasData would
            // report only Croatian as a language for this alias, although it is also available in English and that
            // is the actual translation that should be loaded and displayed to the user.
            $data["language_codes"] = $this->gateway->getLocationUrlAliasLanguageCodes(
                $actions,
                $prioritizedLanguageCodes
            );
        }
        /** @var $caseVerifiedPath */
        $data["path"] = $caseVerifiedPath;
        /** @var $forward */
        $data["forward"] = $forward;
        $data["destination"] = $destination;
        /** @var $isFallback */
        $data["is_fallback"] = $isFallback;
        $data["always_available"] = (bool)( $data["lang_mask"] & 1 );

        return $this->mapper->extractUrlAliasFromData( $data );
    }

    /**
     * Returns all URL alias pointing to the the given location
     *
     * @param mixed $locationId
     * @param array $prioritizedLanguageCodes
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias[]
     */
    public function reverseLookup( $locationId, array $prioritizedLanguageCodes )
    {
        return $this->listURLAliasesForLocation( $locationId, true, $prioritizedLanguageCodes );
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
     * @todo: pass settings and implement eZCharTransform
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
