<?php
/**
 * File containing the eZ\Publish\Core\Repository\URLWildcardService class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\Core\Repository
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\URLWildcardService as URLWildcardServiceInterface,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler,
    eZ\Publish\API\Repository\Values\Content\URLWildcard,
    eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult,
    eZ\Publish\SPI\Persistence\Content\UrlWildcard as SPIUrlWildcard,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\Core\Base\Exceptions\ContentValidationException,
    eZ\Publish\Core\Base\Exceptions\UnauthorizedException;

/**
 * URLAlias service
 *
 * @example Examples/urlalias.php
 *
 * @package eZ\Publish\Core\Repository
 */
class URLWildcardService implements URLWildcardServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler
     */
    protected $urlWildcardHandler;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler $urlWildcardHandler
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $urlWildcardHandler, array $settings = array() )
    {
        $this->repository = $repository;
        $this->urlWildcardHandler = $urlWildcardHandler;
        $this->settings = $settings + array(// Union makes sure default settings are ignored if provided in argument
            //'defaultSetting' => array(),
        );
    }

    /**
     * creates a new url wildcard
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the $sourceUrl pattern already exists
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create url wildcards
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if the number of "*" patterns in $sourceUrl and
     *         the numbers in {\d} placeholders in $destinationUrl does not match.
     *
     * @param string $sourceUrl
     * @param string $destinationUrl
     * @param boolean $forward
     *
     * @return \eZ\Publish\API\Repository\Values\Content\UrlWildcard
     */
    public function create( $sourceUrl, $destinationUrl, $forward = false )
    {
        if ( $this->repository->hasAccess( 'content', 'urltranslator' ) !== true )
            throw new UnauthorizedException( 'content', 'urltranslator' );

        $sourceUrl = $this->cleanUrl( $sourceUrl );
        $destinationUrl = $this->cleanUrl( $destinationUrl );

        $spiUrlWildcards = $this->urlWildcardHandler->loadAll();
        foreach ( $spiUrlWildcards as $wildcard )
        {
            if ( $wildcard->sourceUrl === $sourceUrl )
            {
                throw new InvalidArgumentException(
                    "\$sourceUrl",
                    "Pattern already exists"
                );
            }
        }

        preg_match_all( '(\\*)', $sourceUrl, $patterns );
        preg_match_all( '(\{(\d+)\})', $destinationUrl, $placeholders );

        $patterns = array_map( 'intval', $patterns[0] );
        $placeholders = array_map( 'intval', $placeholders[1] );

        if ( count( $placeholders ) > 0 && max( $placeholders ) > count( $patterns ) )
        {
            throw new ContentValidationException( "Placeholders are not matching with wildcards." );
        }

        $this->repository->beginTransaction();
        try
        {
            $spiUrlWildcard = $this->urlWildcardHandler->create(
                $sourceUrl,
                $destinationUrl,
                $forward
            );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildUrlWildcardDomainObject( $spiUrlWildcard );
    }

    /**
     * Removes leading and trailing slashes and spaces.
     *
     * @param string $url
     *
     * @return string
     */
    protected function cleanUrl( $url )
    {
        return trim( $url, "/ " );
    }

    /**
     * removes an url wildcard
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to remove url wildcards
     *
     * @param \eZ\Publish\API\Repository\Values\Content\UrlWildcard $urlWildcard the url wildcard to remove
     */
    public function remove( URLWildcard $urlWildcard )
    {
        if ( $this->repository->hasAccess( 'content', 'urltranslator' ) !== true )
            throw new UnauthorizedException( 'content', 'urltranslator' );

        $this->repository->beginTransaction();
        try
        {
            $this->urlWildcardHandler->remove(
                $urlWildcard->id
            );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     *
     * loads a url wild card
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url wild card was not found
     *
     * @param mixed $id
     *
     * @return \eZ\Publish\API\Repository\Values\Content\UrlWildcard
     */
    public function load( $id )
    {
        return $this->buildUrlWildcardDomainObject(
            $this->urlWildcardHandler->load( $id )
        );
    }

    /**
     * loads all url wild card (paged)
     *
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\Content\UrlWildcard[]
     */
    public function loadAll( $offset = 0, $limit = -1 )
    {
        $spiUrlWildcards = $this->urlWildcardHandler->loadAll(
            $offset,
            $limit
        );

        $urlWildcards = array();
        foreach ( $spiUrlWildcards as $spiUrlWildcard )
        {
            $urlWildcards[] = $this->buildUrlWildcardDomainObject( $spiUrlWildcard );
        }

        return $urlWildcards;
    }

    /**
     * Translates an url to an existing uri resource based on the
     * source/destination patterns of the url wildcard.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url could not be translated
     *
     * @param mixed $url
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult
     */
    public function translate( $url )
    {
        $spiUrlWildcards = $this->urlWildcardHandler->loadAll();

        // sorts wildcards by length of source URL string
        // @todo sort by specificity of the pattern?
        uasort(
            $spiUrlWildcards,
            function( SPIUrlWildcard $w1, SPIUrlWildcard $w2 )
            {
                return strlen( $w2->sourceUrl ) - strlen( $w1->sourceUrl );
            }
        );

        foreach ( $spiUrlWildcards as $wildcard )
        {
            if ( $uri = $this->match( $url, $wildcard ) )
            {
                return new URLWildcardTranslationResult(
                    array(
                        'uri' => $uri,
                        'forward' => $wildcard->forward
                    )
                );
            }
        }

        throw new NotFoundException( "URLWildcard", $url );
    }

    /**
     * Map by specificity
     *
     * @param \eZ\Publish\SPI\Persistence\Content\UrlWildcard[] $spiUrlWildcards
     *
     * @return array
     * @todo use or remove
     */
    private function buildSpecificityScoreMap( array $spiUrlWildcards )
    {
        $map = array();

        foreach ( $spiUrlWildcards as $spiUrlWildcard )
        {
            $map[$spiUrlWildcard->id] = preg_replace("/[\\D]/", "", strtr( $spiUrlWildcard->sourceUrl, "/*", "10" ) );
        }

        return $map;
    }

    /**
     * Tests if the given url matches against the given url wildcard.
     *
     * if the wildcard matches on the given url this method will return a ready
     * to use destination url, otherwise this method will return <b>NULL</b>.
     *
     * @param string $url
     * @param \eZ\Publish\SPI\Persistence\Content\UrlWildcard $wildcard
     *
     * @return null|string
     */
    private function match( $url, SPIUrlWildcard $wildcard )
    {
        if ( preg_match( $this->compile( $wildcard->sourceUrl ), $url, $match ) )
        {
            return $this->substitute( $wildcard->destinationUrl, $match );
        }
        return null;
    }

    /**
     * Compiles the given url pattern into a regular expression.
     *
     * @param string $sourceUrl
     * @return string
     */
    private function compile( $sourceUrl )
    {
        return '(^' . str_replace( '\\*', '(.*)', preg_quote( $sourceUrl ) ) . '$)U';
    }

    /**
     * Substitutes all placeholders ({\d}) in the given <b>$destinationUrl</b> with
     * the values from the given <b>$values</b> array.
     *
     * @param string $destinationUrl
     * @param array $values
     * @todo remove throw?
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @return string
     */
    private function substitute( $destinationUrl, array $values )
    {
        preg_match_all( '(\{(\d+)\})', $destinationUrl, $matches );

        foreach ( $matches[1] as $match )
        {
            $destinationUrl = str_replace( "{{$match}}", $values[$match], $destinationUrl );
        }
        return $destinationUrl;
    }

    /**
     * Builds API UrlWildcard object from given SPI UrlWildcard object
     *
     * @param \eZ\Publish\SPI\Persistence\Content\UrlWildcard $wildcard
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLWildcard
     */
    private function buildUrlWildcardDomainObject( SPIUrlWildcard $wildcard )
    {
        return new URLWildcard(
            array(
                "id" => $wildcard->id,
                "destinationUrl" => $wildcard->destinationUrl,
                "sourceUrl" => $wildcard->sourceUrl,
                "forward" => $wildcard->forward
            )
        );
    }
}
