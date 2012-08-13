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
    eZ\Publish\SPI\Persistence\Handler,
    eZ\Publish\API\Repository\Values\Content\URLWildcard,
    eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult,
    eZ\Publish\SPI\Persistence\Content\UrlWildcard as SPIUrlWildcard,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\Core\Base\Exceptions\ContentValidationException;

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
        $this->settings = $settings;
    }

    /**
     * creates a new url wildcard
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the $sourceUrl pattern already exists
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create url wildcards
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if the number of "*" patterns in $sourceUrl and
     *          the number of {\d} placeholders in $destinationUrl doesn't match or
     *          if the placeholders aren't a valid number sequence({1}/{2}/{3}), starting with 1.
     *
     * @param string $sourceUrl
     * @param string $destinationUrl
     * @param boolean $forward
     *
     * @return \eZ\Publish\API\Repository\Values\Content\UrlWildcard
     */
    public function create( $sourceUrl, $destinationUrl, $forward = false )
    {
        $spiUrlWildcards = $this->persistenceHandler->urlWildcardHandler()->loadAll();
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

        if ( count( $patterns[0] ) !== count( $placeholders[1] ) )
        {
            throw new ContentValidationException( 'What error code should be used?' );
        }

        $placeholders = array_map( 'intval', $placeholders[1] );
        sort( $placeholders );

        if ( range( 1, count( $placeholders ) ) !== $placeholders  )
        {
            throw new ContentValidationException( 'What error code should be used?' );
        }

        $this->repository->beginTransaction();
        try
        {
            $spiUrlWildcard = $this->persistenceHandler->urlWildcardHandler()->create(
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
     * removes an url wildcard
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to remove url wildcards
     *
     * @param \eZ\Publish\API\Repository\Values\Content\UrlWildcard $urlWildcard the url wildcard to remove
     */
    public function remove( URLWildcard $urlWildcard )
    {
        $this->repository->beginTransaction();
        try
        {
            $this->persistenceHandler->urlWildcardHandler()->remove(
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
            $this->persistenceHandler->urlWildcardHandler()->load( $id )
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
        $spiUrlWildcards = $this->persistenceHandler->urlWildcardHandler()->loadAll(
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
     * translates an url to an existing uri resource based on the
     * source/destination patterns of the url wildcard. If the resulting
     * url is an alias it will be translated to the system uri.
     *
     * This method runs also configured url translations and filter
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url could not be translated
     *
     * @param mixed $url
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult
     */
    public function translate( $url )
    {
        $spiUrlWildcards = $this->persistenceHandler->urlWildcardHandler()->loadAll();

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

        $alias = $this->repository->getURLAliasService()->lookUp( $url );

        return new URLWildcardTranslationResult(
            array(
                'uri' => $alias->path,
                'forward' => $alias->forward
            )
        );
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
    protected function buildUrlWildcardDomainObject( SPIUrlWildcard $wildcard )
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
