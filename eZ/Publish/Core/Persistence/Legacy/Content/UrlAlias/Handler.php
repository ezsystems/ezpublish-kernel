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
    eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway,
    eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler as LanguageCachingHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator,
    eZ\Publish\SPI\Persistence\Content\UrlAlias,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZ\Publish\Core\Base\Exceptions\ForbiddenException,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

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
     * Creates a new UrlWildcard Handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway $gateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper $mapper
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler $languageHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator
     */
    public function __construct(
        Gateway $gateway,
        Mapper $mapper,
        LanguageCachingHandler $languageHandler,
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
     * @param string $name the new name computed by the name schema or url alias schema
     * @param string $languageCode
     * @param boolean $alwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException if the path already exists for the given language
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function publishUrlAliasForLocation( $locationId, $name, $languageCode, $alwaysAvailable = false )
    {
        $action = "eznode:" . $locationId;
        $languageId = $this->languageHandler->getByLocale( $languageCode )->id;
        $row = $this->gateway->loadRowByAction( "eznode:" . $locationId );
        $parentId = empty( $row ) ? 0 : $row["parent"];
        $uniqueCounter = $this->getUniqueCounterValue( $name, $parentId );
        // @todo process filters
        $name = $this->convertToAlias( $name, "location_" . $locationId );

        // If last entry parent is root, reset $parentId to 0 and empty $createdPath
        if ( $parentId != 0 && $this->gateway->isRootEntry( $parentId ) )
        {
            $parentId = 0;
        }

        while ( true )
        {
            $newText = $name . $uniqueCounter > 1 ? $uniqueCounter : "";
            $newTextMD5 = $this->getHash( $newText );
            $row = $this->gateway->loadRow( $parentId, $newTextMD5 );
            if ( empty( $row ) )
            {
                // Set common values
                $data = array(
                    "action" => $action,
                    // Set mask to language with always available bit
                    "lang_mask" => $languageId | (int)$alwaysAvailable,
                    "text" => $newText,
                    "text_md5" => $newTextMD5,
                );
                // Check for system entry on this level
                $systemAliasRow = $this->gateway->loadRowByAction( $action );
                if ( empty( $systemAliasRow ) )
                {
                    // There is no system entry on this level, insert new row
                    $newElementId = $this->gateway->insertRow( $data );
                }
                else
                {
                    // System entry on this level exists, reuse it
                    $newElementId = $systemAliasRow["id"];
                    $this->gateway->updateRow(
                        $parentId,
                        $newTextMD5,
                        $data + array(
                            "id" => $newElementId,
                            "link" => $newElementId
                        )
                    );
                }

                break;
            }
            // Check if row is reusable
            if ( $row["action"] == "nop:" || $row["action"] == $action || $row["is_original"] == 0 )
            {
                // Check for existing system entry on this level, if it's id differs from reusable entry id then
                // reusable entry is history and should be updated with the system entry id
                // Same id for location entries are needed to choose most prioritized row when translating URL
                // Note: system entry will be moved to history or downgraded later
                $systemAliasRow = $this->gateway->loadRowByAction( $action );
                $newElementId = ( !empty( $systemAliasRow ) && $systemAliasRow["id"] != $row["id"] ) ?
                    $systemAliasRow["id"] :
                    $row["id"];
                $this->gateway->updateRow(
                    $parentId,
                    $newTextMD5,
                    array(
                        "action" => $action,
                        // Add language and always available bit to the mask
                        "lang_mask" => ( $row["lang_mask"] & ~1 ) | $languageId | (int)$alwaysAvailable,
                        "text" => $newText,
                        "text_md5" => $newTextMD5,
                        "id " => $newElementId,
                        "link " => $newElementId
                    )
                );

                break;
            }
            $uniqueCounter += 1;
        }

        // Cleanup
        $this->gateway->downgrade( $newElementId, $action, $parentId, $newTextMD5, $languageId );
        $this->gateway->relink( $newElementId, $action, $parentId, $newTextMD5, $languageId );
        $this->gateway->reparent( $newElementId, $action, $parentId, $newTextMD5, $languageId );

        $data["type"] = UrlAlias::LOCATION;
        $data["path"] = $this->gateway->getPath( $newElementId, array( $languageCode ) );
        $data["forward"] = false;
        $data["destination"] = $locationId;
        $data["always_available"] = $alwaysAvailable;
        $data["is_original"] = true;
        $data["is_alias"] = false;
        foreach ( $this->languageMaskGenerator->extractLanguageIdsFromMask( $data["lang_mask"] ) as $languageId )
        {
            $data["language_codes"][] = $this->languageHandler->getById( $languageId )->languageCode;
        }

        return $data;
    }

    /**
     * Create a user chosen $alias pointing to $locationId in $languageName.
     *
     * If $languageName is null the $alias is created in the system's default
     * language. $alwaysAvailable makes the alias available in all languages.
     *
     * @param mixed $locationId
     * @param string $path
     * @param boolean $forwarding
     * @param string $languageCode
     * @param boolean $alwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException if the path already exists for the given language
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function createCustomUrlAlias( $locationId, $path, $forwarding = false, $languageCode = null, $alwaysAvailable = false )
    {
        return $this->mapper->extractUrlAliasFromRow(
            $this->createUrlAlias(
                "eznode:" . $locationId,
                $path,
                $forwarding,
                $languageCode,
                $alwaysAvailable
            )
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
     * @param string $resource
     * @param string $path
     * @param boolean $forwarding
     * @param string $languageCode
     * @param boolean $alwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException if the path already exists for the given language
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function createGlobalUrlAlias( $resource, $path, $forwarding = false, $languageCode = null, $alwaysAvailable = false )
    {
        if ( !preg_match( "#^([a-zA-Z0-9_]+):(.+)$#", $resource, $matches ) )
        {
            throw new InvalidArgumentException( "\$resource", "argument is not valid" );
        }

        if ( $matches[1] === "eznode" )
        {
            return $this->createCustomUrlAlias( $matches[2], $path, $forwarding, $languageCode, $alwaysAvailable );
        }

        return $this->mapper->extractUrlAliasFromRow(
            $this->createUrlAlias(
                $resource,
                $path,
                $forwarding,
                $languageCode,
                $alwaysAvailable
            )
        );
    }

    /**
     * List of url entries of $urlType, pointing to $locationId.
     *
     * @param mixed $locationId
     * @param boolean $custom if true the user generated aliases are listed otherwise the autogenerated
     * @param array $prioritizedLanguageCodes
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias[]
     */
    public function listURLAliasesForLocation( $locationId, $custom = false, array $prioritizedLanguageCodes )
    {
        return $this->mapper->extractUrlAliasListFromRows(
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
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     *
     * @param $url
     * @param string[] $prioritizedLanguageCodes
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function lookup( $url, array $prioritizedLanguageCodes )
    {
        $urlElements = explode( $url, "/" );
        $urlElementsCount = count( $urlElements );
        $data = $this->gateway->loadBasicUrlAliasData( $urlElements, $prioritizedLanguageCodes );

        if ( !empty( $data ) )
        {
            $forward = false;
            $forwardToAction = false;
            $caseVerifiedPathParts = array();
            $destination = false;
            $type = UrlAlias::LOCATION;

            for ( $i = 0; $i < $urlElementsCount; ++$i )
            {
                $caseVerifiedPathParts[] = $data["ezurlalias_ml{$i}_text"];
            }

            // Determine forward flag
            if ( $data["link"] != $data["id"] )
            {
                // If last URL element entry does not link to its "id", redirection to linked entry action
                // should be performed
                $forward = true;
            }
            elseif ( $data["is_alias"] )
            {
                if ( preg_match( "#^module:(.+)$#", $data["action"] ) )
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
                ( $forward && $type !== UrlAlias::RESOURCE ) ||
                ( !$forward && strcmp( $url, implode( "/", $caseVerifiedPathParts ) ) !== 0 )
            )
            {
                // Set $forward to true here for case when path was not case verified
                $forward = true;
                // When redirecting type can be UrlAlias::VIRTUAL or UrlAlias::RESOURCE
                // In latter case path was not case verified and redirect is done to the case-correct path
                $type = UrlAlias::VIRTUAL;

                $destination = $this->gateway->getPath(
                    $forwardToAction ?
                        $this->gateway->getDestinationIdByAction( $data["action"] ) :
                        $data["link"],
                    $prioritizedLanguageCodes
                );
            }
            else
            {
                // In all other cases just translate action to destination (done in Mapper)
                // Note: if $forward is true this will be executed only for resource (global) aliases,
                // in all other cases $forward will be false
            }

            $data["path"] = $url;
            $data["type"] = $type;
            $data["forward"] = $forward;
            $data["destination"] = $destination;
            $data["always_available"] = (bool)( $data["lang_mask"] & 1 );
            foreach ( $this->languageMaskGenerator->extractLanguageIdsFromMask( $data["lang_mask"] ) as $languageId )
            {
                $data["language_codes"][] = $this->languageHandler->getById( $languageId )->languageCode;
            }
        }
        else
        {
            throw new NotFoundException( "url", $url );
        }

        if ( $destination === false )
        {
            throw new NotFoundException( "url", $url );
        }

        $spiUrlAlias = $this->mapper->extractUrlAliasFromRow( $data );

        return $spiUrlAlias;
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
     *
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
    protected function createUrlAlias( $action, $path, $forward, $languageCode = null, $alwaysAvailable = false )
    {
        $pathElements = explode( "/", $path );
        $topElement = array_pop( $pathElements );
        $languageId = $this->languageHandler->getByLocale( $languageCode )->id;
        $createdPath = array();
        $parentId = 0;

        foreach ( $pathElements as $pathElement )
        {
            $pathElement = $this->convertToAlias( $pathElement, "noname" . count( $createdPath ) + 1 );
            $pathElementMD5 = $this->getHash( $pathElement );
            $row = $this->gateway->loadRow( $parentId, $pathElementMD5 );
            $parentId = empty( $row ) ?
                $this->gateway->insertNopRow( $parentId, $pathElement ) :
                $row["link"];
            $createdPath[] = $pathElement;
        }

        $topElement = $this->convertToAlias( $topElement, "noname" . count( $createdPath ) + 1 );

        // If last entry parent is root, reset $parentId to 0 and empty $createdPath
        if ( $parentId != 0 && $this->gateway->isRootEntry( $parentId ) )
        {
            $parentId = 0;
            $createdPath = array();
        }

        // Set common values
        $data = array(
            "action" => $action,
            "is_alias" => 1,
            "alias_redirects" => $forward ? 1 : 0
        );
        while ( true )
        {
            $topElementMD5 = $this->getHash( $topElement );
            $row = $this->gateway->loadRow( $parentId, $topElementMD5 );
            if ( empty( $row ) )
            {
                $this->gateway->insertRow(
                    $data + array(
                        // Set mask to language and always available bit
                        "lang_mask" => $languageId | (int)$alwaysAvailable,
                        "text" => $topElement,
                        "text_md5" => $topElementMD5,
                        "parent" => $parentId
                    )
                );

                break;
            }
            // Check if row is reusable
            if (
                ( $row["action"] == $action && $row["id"] == $row["link"] ) ||
                $row["action"] == "nop:" ||
                $row["is_original"] == 0
            )
            {
                $this->gateway->updateRow(
                    $parentId,
                    $topElementMD5,
                    $data + array(
                        // Add language and always available bit to the mask
                        "lang_mask" => $row["lang_mask"] | $languageId | (int)$alwaysAvailable,
                        "text" => $topElement,
                        "text_md5" => $topElementMD5
                    )
                );

                break;
            }

            throw new ForbiddenException( "Path already exists" );
        }

        $createdPath[] = $topElement;

        $data["type"] = UrlAlias::LOCATION;
        $data["path"] = implode( "/", $createdPath );
        $data["forward"] = $forward;
        $data["destination"] = $data["path"];
        $data["always_available"] = $alwaysAvailable;
        $data["is_original"] = true;
        $data["is_alias"] = true;
        foreach ( $this->languageMaskGenerator->extractLanguageIdsFromMask( $data["lang_mask"] ) as $languageId )
        {
            $data["language_codes"][] = $this->languageHandler->getById( $languageId )->languageCode;
        }

        return $this->mapper->extractUrlAliasFromRow( $data );
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
    protected function convertToAlias( $urlElement, $defaultValue )
    {
        /*
        $trans = eZCharTransform::instance();

        $ini = eZINI::instance();
        $group = $ini->variable( 'URLTranslator', 'TransformationGroup' );

        $urlElement = $trans->transformByGroup( $urlElement, $group );
        */
        if ( strlen( $urlElement ) == 0 )
        {
            if ( $defaultValue === false )
                $urlElement = '_1';
            else
            {
                $urlElement = $defaultValue;
                /*
                $urlElement = $trans->transformByGroup( $urlElement, $group );
                */
            }
        }
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
            // @todo: get reserved names from settings
            $reservedNames = array();
            foreach ( $reservedNames as $reservedName )
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
