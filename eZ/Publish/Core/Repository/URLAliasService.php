<?php
/**
 * File containing the eZ\Publish\Core\Repository\URLAliasService class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\Core\Repository
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\URLAliasService as URLAliasServiceInterface;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\SPI\Persistence\Content\URLAlias as SPIURLAlias;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\ForbiddenException;
use Exception;

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
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + array(
            "showAllTranslations" => false
        );
        // Get prioritized languages from language service to not have to call it several times
        $this->settings['prioritizedLanguageList'] = $repository->getContentLanguageService()->getPrioritizedLanguageCodeList();
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
     * Create a user chosen $alias pointing to a resource in $languageCode.
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
                $matches[1] . ":" . $this->cleanUrl( $matches[2] ),
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
            throw new InvalidArgumentException( "\$path", $e->getMessage(), $e );
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildUrlAliasDomainObject( $spiUrlAlias, $path );
    }

    /**
     * List of url aliases pointing to $location, sorted by language priority.
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
            if ( !$this->isUrlAliasLoadable( $spiUrlAlias, $languageCode ) )
            {
                continue;
            }

            $path = $this->extractPath( $spiUrlAlias, $languageCode );
            if ( $path === false )
            {
                continue;
            }

            $urlAliasList[$spiUrlAlias->id] = $this->buildUrlAliasDomainObject( $spiUrlAlias, $path );
        }

        $prioritizedAliasList = array();
        foreach ( $this->settings["prioritizedLanguageList"] as $languageCode )
        {
            foreach ( $urlAliasList as $urlAlias )
            {
                foreach ( $urlAlias->languageCodes as $aliasLanguageCode )
                {
                    if ( $aliasLanguageCode === $languageCode )
                    {
                        $prioritizedAliasList[$urlAlias->id] = $urlAlias;
                        break;
                    }
                }
            }
        }

        // Add aliases not matched by prioritized language to the end of the list
        return array_values( $prioritizedAliasList + $urlAliasList );
    }

    /**
     * Determines alias language code.
     *
     * Method will return false if language code can't be matched against alias language codes or language settings.
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
     * Returns path extracted from normalized path data returned from persistence, using language settings.
     *
     * Will return false if path could not be determined.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\URLAlias $spiUrlAlias
     * @param string $languageCode
     *
     * @return string|boolean
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

            $pathData[$level] = $levelEntries["translations"][$prioritizedLanguageCode];
        }

        return implode( "/", $pathData );
    }

    /**
     * Returns language code with highest priority.
     *
     * Will return false if language code could nto be matched with language settings in place.
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
     * Matches path string with normalized path data returned from persistence.
     *
     * Returns matched path string (possibly case corrected) and array of corresponding language codes or false
     * if path could not be matched.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\URLAlias $spiUrlAlias
     * @param string $path
     * @param string $languageCode
     *
     * @return array
     */
    protected function matchPath( SPIURLAlias $spiUrlAlias, $path, $languageCode )
    {
        $matchedPathElements = array();
        $matchedPathLanguageCodes = array();
        $pathElements = explode( "/", $path );
        $pathLevels = count( $spiUrlAlias->pathData );

        foreach ( $pathElements as $level => $pathElement )
        {
            if ( $level === $pathLevels - 1 )
            {
                $matchedLanguageCode = $this->selectAliasLanguageCode( $spiUrlAlias, $languageCode );
            }
            else
            {
                $matchedLanguageCode = $this->matchLanguageCode( $spiUrlAlias->pathData[$level], $pathElement );
            }

            if ( $matchedLanguageCode === false )
            {
                return array( false, false );
            }

            $matchedPathLanguageCodes[] = $matchedLanguageCode;
            $matchedPathElements[] = $spiUrlAlias->pathData[$level]["translations"][$matchedLanguageCode];
        }

        return array( implode( "/", $matchedPathElements ), $matchedPathLanguageCodes );
    }

    /**
     * @param array $pathElementData
     * @param string $pathElement
     *
     * @return string|boolean
     */
    protected function matchLanguageCode( array $pathElementData, $pathElement )
    {
        foreach ( $pathElementData["translations"] as $languageCode => $translation )
        {
            if ( strtolower( $pathElement ) === strtolower( $translation ) )
            {
                return $languageCode;
            }
        }

        return false;
    }

    /**
     * Returns true or false depending if URL alias is loadable or not for language settings in place.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\URLAlias $spiUrlAlias
     * @param string|null $languageCode
     *
     * @return boolean
     */
    protected function isUrlAliasLoadable( SPIURLAlias $spiUrlAlias, $languageCode )
    {
        if ( isset( $languageCode ) && !in_array( $languageCode, $spiUrlAlias->languageCodes ) )
        {
            return false;
        }

        if ( $this->settings["showAllTranslations"] )
        {
            return true;
        }

        foreach ( $spiUrlAlias->pathData as $levelPathData )
        {
            if ( $levelPathData["always-available"] )
            {
                continue;
            }

            foreach ( $levelPathData["translations"] as $translationLanguageCode => $translation )
            {
                if ( in_array( $translationLanguageCode, $this->settings["prioritizedLanguageList"] ) )
                {
                    continue 2;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Returns true or false depending if URL alias is loadable or not for language settings in place.
     *
     * @param array $pathData
     * @param array $languageCodes
     *
     * @return boolean
     */
    protected function isPathLoadable( array $pathData, array $languageCodes )
    {
        if ( $this->settings["showAllTranslations"] )
        {
            return true;
        }

        foreach ( $pathData as $level => $levelPathData )
        {
            if ( $levelPathData["always-available"] )
            {
                continue;
            }

            if ( in_array( $languageCodes[$level], $this->settings["prioritizedLanguageList"] ) )
            {
                continue;
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

    /**
     * Builds persistence domain object.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\URLAlias $urlAlias
     *
     * @return \eZ\Publish\SPI\Persistence\Content\URLAlias
     */
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

        $spiUrlAlias = $this->urlAliasHandler->lookup( $url );

        list( $path, $languageCodes ) = $this->matchPath( $spiUrlAlias, $url, $languageCode );
        if ( $path === false || !$this->isPathLoadable( $spiUrlAlias->pathData, $languageCodes ) )
        {
            throw new NotFoundException( "URLAlias", $url );
        }

        return $this->buildUrlAliasDomainObject( $spiUrlAlias, $path );
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
        $urlAliases = $this->listLocationAliases( $location, false, $languageCode );

        foreach ( $this->settings["prioritizedLanguageList"] as $prioritizedLanguageCode )
        {
            foreach ( $urlAliases as $urlAlias )
            {
                if ( in_array( $prioritizedLanguageCode, $urlAlias->languageCodes ) )
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

        throw new NotFoundException( "URLAlias", $location->id );
    }

    /**
     * Loads URL alias by given $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @param string $id
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function load( $id )
    {
        $spiUrlAlias = $this->urlAliasHandler->loadUrlAlias( $id );
        $path = $this->extractPath( $spiUrlAlias, null );

        if ( $path === false )
        {
            throw new NotFoundException( "URLAlias", $id );
        }

        return $this->buildUrlAliasDomainObject( $spiUrlAlias, $path );
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
     * Builds API UrlAlias object from given SPI UrlAlias object
     *
     * @param \eZ\Publish\SPI\Persistence\Content\URLAlias $spiUrlAlias
     * @param string|null $path
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    protected function buildUrlAliasDomainObject( SPIURLAlias $spiUrlAlias, $path )
    {
        return new URLAlias(
            array(
                "id" => $spiUrlAlias->id,
                "type" => $spiUrlAlias->type,
                "destination" => $spiUrlAlias->destination,
                "languageCodes" => $spiUrlAlias->languageCodes,
                "alwaysAvailable" => $spiUrlAlias->alwaysAvailable,
                "path" => "/" . $path,
                "isHistory" => $spiUrlAlias->isHistory,
                "isCustom" => $spiUrlAlias->isCustom,
                "forward" => $spiUrlAlias->forward
            )
        );
    }
}
