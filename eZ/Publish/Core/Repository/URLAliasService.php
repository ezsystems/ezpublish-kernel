<?php
/**
 * File containing the eZ\Publish\Core\Repository\URLAliasService class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\Core\Repository
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\URLAliasService as URLAliasServiceInterface,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\SPI\Persistence\Handler,
    eZ\Publish\API\Repository\Values\Content\Location,
    eZ\Publish\API\Repository\Values\Content\URLAlias,
    eZ\Publish\SPI\Persistence\Content\URLAlias as SPIURLAlias,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * URLAlias service
 *
 * @example Examples/urlalias.php
 *
 * @package eZ\Publish\Core\Repository
 */
class URLAliasService implements URLAliasServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $handler, array $settings = array() )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
        $this->settings = $settings + array(
            "prioritizedLanguageList" => array(
                "eng-US"
            )
        );
    }

     /**
     * Create a user chosen $alias pointing to $location in $languageCode.
     *
     * This method runs URL filters and transformers before storing them.
     * Hence the path returned in the URLAlias Value may differ from the given.
     * $alwaysAvailable makes the alias available in all languages.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $path
     * @param boolean $forward if true a redirect is performed
     * @param string $languageCode the languageCode for which this alias is valid
     * @param boolean $alwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the path already exists for the given language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function createUrlAlias( Location $location, $path, $languageCode, $forward = false, $alwaysAvailable = false )
    {
        $path = $this->cleanUrl( $path );
        $path = $this->addPathPrefix( $path );

        $this->repository->beginTransaction();
        $spiUrlAlias = $this->internalCreateUrlAlias( $location->id, $path, $languageCode, $forward, $alwaysAvailable );
        $this->repository->commit();

        return $this->buildUrlAliasDomainObject( $spiUrlAlias );
    }

    /**
     * Internal method for creating URL alias
     *
     * Reused by self::createUrlAlias and self::createGlobalUrlAlias if $resource is "eznode"
     *
     * @param $locationId
     * @param $path
     * @param $languageCode
     * @param $forward
     * @param $alwaysAvailable
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function internalCreateUrlAlias( $locationId, $path, $languageCode, $forward, $alwaysAvailable )
    {
        return $this->persistenceHandler->urlAliasHandler()->createCustomUrlAlias(
            $locationId,
            $path,
            $forward,
            $languageCode,
            $alwaysAvailable
        );
    }

     /**
     * Create a user chosen $alias pointing to a resource in $languageName.
     *
     * This method does not handle location resources - if a user enters a location target
     * the createCustomUrlAlias method has to be used.
     * This method runs URL filters and and transformers before storing them.
     * Hence the path returned in the URLAlias Value may differ from the given.
     *
     * $alwaysAvailable makes the alias available in all languages.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the path already exists for the given language
     *
     * @param string $resource
     * @param string $path
     * @param boolean $forward
     * @param string $languageCode
     * @param boolean $alwaysAvailable
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function createGlobalUrlAlias( $resource, $path, $languageCode, $forward = false, $alwaysAvailable = false )
    {
        if ( !preg_match( "#^([a-zA-Z0-9_]+):(.+)$#", $resource, $matches ) )
        {
            throw new InvalidArgumentException( "\$resource", "argument is not valid" );
        }

        $path = $this->cleanUrl( $path );
        $path = $this->addPathPrefix( $path );

        // @todo handle module:content/view/full/<id>
        if ( $matches[1] === "eznode" )
        {
            return $this->internalCreateUrlAlias(
                $matches[2],
                $path,
                $languageCode,
                $forward,
                $alwaysAvailable
            );
        }

        $this->repository->beginTransaction();
        $spiUrlAlias = $this->persistenceHandler->urlAliasHandler()->createGlobalUrlAlias(
            $resource,
            $path,
            $forward,
            $languageCode,
            $alwaysAvailable
        );
        $this->repository->beginTransaction();

        return $this->buildUrlAliasDomainObject( $spiUrlAlias );
    }

     /**
     * List of url aliases pointing to $location.
     *
     * @todo may be there is a need for a function which returns one URL Alias based on a prioritized language
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param boolean $custom if true the user generated aliases are listed otherwise the autogenerated
     * @param string $languageCode filters those which are valid for the given language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias[]
     * @todo get prioritized languages from ini
     */
    public function listLocationAliases( Location $location, $custom = true, $languageCode = null )
    {
        if ( isset( $languageCode ) )
        {
            $prioritizedLanguageCodes = array( $languageCode );
        }
        else
        {
            // @todo get from settings
            $prioritizedLanguageCodes = array( $languageCode );
        }

        $spiUrlAliasList = $this->persistenceHandler->urlAliasHandler()->listURLAliasesForLocation(
            $location->id,
            $custom,
            $prioritizedLanguageCodes
        );

        $urlAliasList = array();
        foreach ( $spiUrlAliasList as $spiUrlAlias )
        {
            $urlAliasList[] = $this->buildUrlAliasDomainObject( $spiUrlAlias );
        }

        return $urlAliasList;
    }

    /**
     * List global aliases
     *
     * @param string $languageCode filters those which are valid for the given language
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias[]
     */
    public function listGlobalAliases( $languageCode = null, $offset = 0, $limit = -1 )
    {

    }

    /**
     * Removes urls aliases.
     *
     * This method does not remove autogenerated aliases for locations.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\URLAlias[] $aliasList
     * @return boolean
     */
    public function removeAliases( array $aliasList )
    {

    }

    /**
     * looks up the URLAlias for the given url.
     *
     * @param string $url
     * @param string $languageCode
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the path does not exist or is not valid for the given language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function lookup( $url, $languageCode = null )
    {
        $url = $this->cleanUrl( $url );
        $url = $this->addPathPrefix( $url );

        if ( isset( $languageCode ) )
        {
            $prioritizedLanguageCodes = array( $languageCode );
        }
        else
        {
            //@TODO: get prioritized languages from ini
            $prioritizedLanguageCodes = $this->settings["prioritizedLanguageList"];
        }

        $spiUrlAlias = $this->persistenceHandler->urlAliasHandler()->lookup(
            $url,
            $prioritizedLanguageCodes
        );

        return $this->buildUrlAliasDomainObject( $spiUrlAlias );
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function cleanUrl( $url )
    {
        return trim( $url, "/ " );
    }

    /**
     * Adds path prefix to URL
     *
     * @param string $url
     *
     * @return string $url with path prefix prepended
     * @todo: implement
     */
    protected function addPathPrefix( $url )
    {
        $pathPrefix = array();
        $pathPrefixExclude = array();

        return $url;
    }

    /**
     * Removes path prefix from URL
     *
     * @param string $url
     *
     * @return string $url with path prefix removed
     * @todo: implement
     */
    protected function removePathPrefix( $url )
    {
        $pathPrefix = array();
        $pathPrefixExclude = array();

        return $url;
    }

    /**
     * Checks if resource string format is valid
     *
     * @param string $resource
     *
     * @return bool
     */
    protected function isResourceValid( $resource )
    {
        return (bool)preg_match( "#^([a-zA-Z0-9_]+):(.+)$#", $resource, $matches );
    }

    /**
     * Builds API UrlAlias object from given SPI UrlAlias object
     *
     * @param \eZ\Publish\SPI\Persistence\Content\URLAlias $spiUrlAlias
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    protected function buildUrlAliasDomainObject( SPIURLAlias $spiUrlAlias )
    {
        return new URLAlias(
            array(
                "id" => $spiUrlAlias->id,
                "type" => $spiUrlAlias->type,
                "destination" => $this->removePathPrefix( $spiUrlAlias->destination ),
                "languageCodes" => $spiUrlAlias->languageCodes,
                "alwaysAvailable" => $spiUrlAlias->alwaysAvailable,
                "path" => $spiUrlAlias->path,
                "isHistory" => $spiUrlAlias->isHistory,
                "isCustom" => $spiUrlAlias->isCustom,
                "forward" => $spiUrlAlias->forward
            )
        );
    }
}
