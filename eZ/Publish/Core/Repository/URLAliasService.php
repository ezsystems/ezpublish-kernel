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
    eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler,
    eZ\Publish\API\Repository\Values\Content\Location,
    eZ\Publish\API\Repository\Values\Content\URLAlias,
    eZ\Publish\SPI\Persistence\Content\URLAlias as SPIURLAlias,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\API\Repository\Exceptions\ForbiddenException,
    Exception;

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
     * @var \eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler
     */
    protected $urlAliasHandler;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler $urlAliasHandler
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $urlAliasHandler, array $settings = array() )
    {
        $this->repository = $repository;
        $this->urlAliasHandler = $urlAliasHandler;
        $this->settings = $settings + array(// Union makes sure default settings are ignored if provided in argument
            'prioritizedLanguageList' => array(// @todo These settings should probably be exposed from Language Service
                "eng-US",
                "eng-GB"
            ),
            "showAllTranslations" => false
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
     * @param boolean $forwarding if true a redirect is performed
     * @param string $languageCode the languageCode for which this alias is valid
     * @param boolean $alwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the path already exists for the given language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function createUrlAlias( Location $location, $path, $languageCode, $forwarding = false, $alwaysAvailable = false )
    {
        $path = $this->cleanUrl( $path );
        $path = $this->addPathPrefix( $path );

        $this->repository->beginTransaction();
        try
        {
            $spiUrlAlias = $this->urlAliasHandler->createCustomUrlAlias(
                $location->id,
                $path,
                $forwarding,
                $languageCode,
                $alwaysAvailable
            );
            $this->repository->commit();
        }
        catch ( ForbiddenException $e )
        {
            $this->repository->rollback();
            throw new InvalidArgumentException(
                "\$path",
                $e->getMessage(),
                $e
            );
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildUrlAliasDomainObject( $spiUrlAlias, $path );
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
      * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the path already exists for the given
      *         language or if resource is not valid
     *
     * @param string $resource
     * @param string $path
     * @param string $languageCode
     * @param boolean $forwarding
     * @param boolean $alwaysAvailable
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function createGlobalUrlAlias( $resource, $path, $languageCode, $forwarding = false, $alwaysAvailable = false )
    {
        if ( !preg_match( "#^([a-zA-Z0-9_]+):(.+)$#", $resource, $matches ) )
        {
            throw new InvalidArgumentException( "\$resource", "argument is not valid" );
        }

        $path = $this->cleanUrl( $path );
        $path = $this->addPathPrefix( $path );

        if ( $matches[1] === "eznode" || 0 === strpos( $matches[2], "module:content/view/full/" ) )
        {
            if ( $matches[1] === "eznode" )
            {
                $locationId = $matches[2];
            }
            else
            {
                $resourcePath = explode( "/", $matches[2] );
                $locationId = end( $resourcePath );
            }

            return $this->createUrlAlias(
                $locationId,
                $path,
                $languageCode,
                $forwarding,
                $alwaysAvailable
            );
        }

        $this->repository->beginTransaction();
        try
        {
            $spiUrlAlias = $this->urlAliasHandler->createGlobalUrlAlias(
                $resource,
                $path,
                $forwarding,
                $languageCode,
                $alwaysAvailable
            );
            $this->repository->commit();
        }
        catch ( ForbiddenException $e )
        {
            $this->repository->rollback();
            throw new InvalidArgumentException(
                "\$path",
                $e->getMessage(),
                $e
            );
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildUrlAliasDomainObject( $spiUrlAlias, $path );
    }

     /**
     * List of url aliases pointing to $location.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param boolean $custom if true the user generated aliases are listed otherwise the autogenerated
     * @param string $languageCode filters those which are valid for the given language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias[]
     */
    public function listLocationAliases( Location $location, $custom = true, $languageCode = null )
    {
        $urlAliasList = array();
        $spiUrlAliasList = $this->urlAliasHandler->listURLAliasesForLocation(
            $location->id,
            $custom
        );

        foreach ( $spiUrlAliasList as $spiUrlAlias )
        {
            $path = $this->extractPath( $spiUrlAlias, $languageCode );
            if ( $path === false )
            {
                continue;
            }

            $urlAliasList[] = $this->buildUrlAliasDomainObject( $spiUrlAlias, $path );
        }

        return $urlAliasList;
    }

    /**
     * Determines alias language code.
     *
     * Method will return false if language code can't be determined.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\URLAlias $spiUrlAlias
     * @param string|null $languageCode
     *
     * @return string|boolean
     */
    protected function selectAliasLanguageCode( SPIURLAlias $spiUrlAlias, $languageCode )
    {
        if ( isset( $languageCode ) && !in_array( $languageCode, $spiUrlAlias->languageCodes ) )
        {
            return false;
        }

        foreach ( $this->settings["prioritizedLanguageList"] as $languageCode )
        {
            if ( in_array( $languageCode, $spiUrlAlias->languageCodes ) )
            {
                return $languageCode;
            }
        }

        if ( $spiUrlAlias->alwaysAvailable || $this->settings["showAllTranslations"] )
        {
            $lastLevelData = end( $spiUrlAlias->pathData );
            return key( $lastLevelData["translations"] );
        }

        return false;
    }

    /**
     *
     *
     * @param \eZ\Publish\SPI\Persistence\Content\URLAlias $spiUrlAlias
     * @param string $languageCode
     *
     * @return string
     */
    protected function extractPath( SPIURLAlias $spiUrlAlias, $languageCode )
    {
        $pathData = array();
        $pathLevels = count( $spiUrlAlias->pathData );

        foreach ( $spiUrlAlias->pathData as $level => $levelEntries )
        {
            if ( $level === $pathLevels - 1 )
            {
                $prioritizedLanguageCode = $this->selectAliasLanguageCode( $spiUrlAlias, $languageCode );
            }
            else
            {
                $prioritizedLanguageCode = $this->choosePrioritizedLanguageCode( $levelEntries );
            }

            if ( $prioritizedLanguageCode === false )
            {
                return false;
            }

            $pathData[$level] =  $levelEntries["translations"][$prioritizedLanguageCode];
        }

        return implode( "/", $pathData );
    }

    /**
     *
     *
     * @param array $entries
     *
     * @return string|boolean
     */
    protected function choosePrioritizedLanguageCode( array $entries )
    {
        foreach ( $this->settings["prioritizedLanguageList"] as $prioritizedLanguageCode )
        {
            if ( isset( $entries["translations"][$prioritizedLanguageCode] ) )
            {
                return $prioritizedLanguageCode;
            }
        }

        if ( $entries["always-available"] || $this->settings["showAllTranslations"] )
        {
            return key( $entries["translations"] );
        }

        return false;
    }

    /**
     *
     *
     * @param \eZ\Publish\SPI\Persistence\Content\URLAlias $spiUrlAlias
     *
     * @return string
     */
    protected function extractPathByPathLanguageData( SPIURLAlias $spiUrlAlias )
    {
        $pathData = array();

        foreach ( $spiUrlAlias->pathData as $level => $levelEntries )
        {
            $languageCode = reset( $spiUrlAlias->pathLanguageData[$level]["language-codes"] );
            $pathData[] = $levelEntries["translations"][$languageCode];
        }

        return implode( "/", $pathData );
    }

    /**
     *
     *
     * @param \eZ\Publish\SPI\Persistence\Content\URLAlias $spiUrlAlias
     * @param string|null $languageCode
     *
     * @return boolean
     */
    protected function isAliasLoadable( SPIURLAlias $spiUrlAlias, $languageCode )
    {
        if ( isset( $languageCode ) && !in_array( $languageCode, $spiUrlAlias->languageCodes ) )
        {
            return false;
        }

        if ( $this->settings["showAllTranslations"] )
        {
            return true;
        }

        foreach ( $spiUrlAlias->pathLanguageData as $levelLanguageData )
        {
            if ( $levelLanguageData["always-available"] )
            {
                continue;
            }

            foreach ( $levelLanguageData["language-codes"] as $levelLanguageCode )
            {
                if ( in_array( $levelLanguageCode, $this->settings["prioritizedLanguageList"] ) )
                {
                    continue 2;
                }
            }

            return false;
        }

        return true;
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
        $urlAliasList = array();
        $spiUrlAliasList = $this->urlAliasHandler->listGlobalURLAliases(
            $languageCode,
            $offset,
            $limit
        );

        foreach ( $spiUrlAliasList as $spiUrlAlias )
        {
            $path = $this->extractPath( $spiUrlAlias, $languageCode );
            if ( $path === false )
            {
                continue;
            }

            $urlAliasList[] = $this->buildUrlAliasDomainObject( $spiUrlAlias, $path );
        }

        return $urlAliasList;
    }

    /**
     * Removes urls aliases.
     *
     * This method does not remove autogenerated aliases for locations.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if alias list contains
     *         autogenerated alias
     *
     * @param \eZ\Publish\API\Repository\Values\Content\URLAlias[] $aliasList
     *
     * @return void
     */
    public function removeAliases( array $aliasList )
    {
        $spiUrlAliasList = array();
        foreach ( $aliasList as $alias )
        {
            if ( !$alias->isCustom )
            {
                throw new InvalidArgumentException(
                    "\$aliasList",
                    "Alias list contains autogenerated alias"
                );
            }
            $spiUrlAliasList[] = $this->buildSPIUrlAlias( $alias );
        }

        $this->repository->beginTransaction();
        try
        {
            $this->urlAliasHandler->removeURLAliases( $spiUrlAliasList );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    protected function buildSPIUrlAlias( URLAlias $urlAlias )
    {
        return new SPIURLAlias(
            array(
                "id" => $urlAlias->id,
                "isCustom" => $urlAlias->isCustom
            )
        );
    }

    /**
     * looks up the URLAlias for the given url.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the path does not exist or is not valid for the given language
     *
     * @param string $url
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function lookup( $url, $languageCode = null )
    {
        $url = $this->cleanUrl( $url );
        $url = $this->addPathPrefix( $url );

        $spiUrlAlias = $this->urlAliasHandler->lookup( $url );

        if ( !$this->isAliasLoadable( $spiUrlAlias, $languageCode ) )
        {
            throw new NotFoundException(
                "URLAlias",
                $url
            );
        }

        return $this->buildUrlAliasDomainObject(
            $spiUrlAlias,
            $this->extractPathByPathLanguageData( $spiUrlAlias )
        );
    }

    /**
     * Returns the URL alias for the given location in the given language.
     *
     * If $languageCode is null the method returns the url alias in the most prioritized language.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if no url alias exist for the given language
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function reverseLookup( Location $location, $languageCode = null )
    {
        $urlAliases = $this->listLocationAliases( $location, $languageCode );

        foreach ( $this->settings["prioritizedLanguageList"] as $languageCode )
        {
            foreach ( $urlAliases as $urlAlias )
            {
                if ( in_array( $languageCode, $urlAlias->languageCodes ) )
                {
                    return $urlAlias;
                }
            }
        }

        foreach ( $urlAliases as $urlAlias )
        {
            if ( $urlAlias->alwaysAvailable )
            {
                return $urlAlias;
            }
        }

        throw new NotFoundException(
            "URLAlias",
            $location->id
        );
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
     * Builds API UrlAlias object from given SPI UrlAlias object
     *
     * @param \eZ\Publish\SPI\Persistence\Content\URLAlias $spiUrlAlias
     * @param string|null $path
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    protected function buildUrlAliasDomainObject( SPIURLAlias $spiUrlAlias, $path )
    {
        if ( $spiUrlAlias->type === SPIURLAlias::LOCATION )
        {
            $destination = $this->repository->getLocationService()->loadLocation( $spiUrlAlias->destination );
        }
        else
        {
            $destination = $this->removePathPrefix( $spiUrlAlias->destination );
        }

        return new URLAlias(
            array(
                "id" => $spiUrlAlias->id,
                "type" => $spiUrlAlias->type,
                "destination" => $destination,
                "languageCodes" => $spiUrlAlias->languageCodes,
                "alwaysAvailable" => $spiUrlAlias->alwaysAvailable,
                "path" => $path,
                "isHistory" => $spiUrlAlias->isHistory,
                "isCustom" => $spiUrlAlias->isCustom,
                "forward" => $spiUrlAlias->forward
            )
        );
    }
}
