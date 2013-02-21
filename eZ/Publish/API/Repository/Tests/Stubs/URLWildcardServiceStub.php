<?php
/**
 * File containing the URLWildcardServiceStub class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use eZ\Publish\API\Repository\URLWildcardService;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;
use eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult;
use eZ\Publish\API\Repository\Tests\Stubs\Exceptions\ContentValidationExceptionStub;
use eZ\Publish\API\Repository\Tests\Stubs\Exceptions\InvalidArgumentExceptionStub;
use eZ\Publish\API\Repository\Tests\Stubs\Exceptions\NotFoundExceptionStub;
use eZ\Publish\API\Repository\Tests\Stubs\Exceptions\UnauthorizedExceptionStub;

/**
 * Url wold service stub implementation.
 *
 * @package eZ\Publish\API\Repository\Tests\Stubs
 */
class URLWildcardServiceStub implements URLWildcardService
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\URLWildcard[]
     */
    private $wildcards = array();

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub
     */
    private $repository;

    /**
     * Instantiates a new url wildcard stub.
     *
     * @param RepositoryStub $repository
     */
    public function __construct( RepositoryStub $repository )
    {
        $this->repository = $repository;
    }

    /**
     * Creates a new url wildcard
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
        if ( false === $this->repository->hasAccess( 'content', 'edit' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        foreach ( $this->wildcards as $wildcard )
        {
            if ( $wildcard->sourceUrl === $sourceUrl )
            {
                throw new InvalidArgumentExceptionStub( 'What error code should be used?' );
            }
        }

        preg_match_all( '(\\*)', $sourceUrl, $patterns );
        preg_match_all( '(\{(\d+)\})', $destinationUrl, $placeholders );

        $patterns = array_map( 'intval', $patterns[0] );
        $placeholders = array_map( 'intval', $placeholders[1] );

        if ( count( $placeholders ) > 0 && max( $placeholders ) > count( $patterns ) )
        {
            throw new ContentValidationExceptionStub( 'What error code should be used?' );
        }

        $wildcard = new URLWildcard(
            array(
                'id' => ++$this->id,
                'sourceUrl' => $sourceUrl,
                'destinationUrl' => $destinationUrl,
                'forward' => $forward
            )
        );

        return ( $this->wildcards[$wildcard->id] = $wildcard );
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
        if ( false === $this->repository->canUser( 'content', 'edit', $urlWildcard ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        unset( $this->wildcards[$urlWildcard->id] );
    }

    /**
     * Loads a url wild card
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url wild card was not found
     *
     * @param mixed $id
     *
     * @return \eZ\Publish\API\Repository\Values\Content\UrlWildcard
     */
    public function load( $id )
    {
        if ( isset( $this->wildcards[$id] ) )
        {
            return $this->wildcards[$id];
        }
        throw new NotFoundExceptionStub( 'What error code should be used?' );
    }

    /**
     * Loads all url wild card (paged)
     *
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\Content\UrlWildcard[]
     */
    public function loadAll( $offset = 0, $limit = -1 )
    {
        return array_slice( $this->wildcards, $offset, -1 === $limit ? PHP_INT_MAX : $limit );
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
        uasort(
            $this->wildcards,
            function ( URLWildcard $w1, URLWildcard $w2 )
            {
                return strlen( $w2->sourceUrl ) - strlen( $w1->sourceUrl );
            }
        );

        foreach ( $this->wildcards as $wildcard )
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

        throw new NotFoundExceptionStub( 'What error code should be used?' );
    }

    /**
     * Tests if the given url matches against the given url wildcard.
     *
     * if the wildcard matches on the given url this method will return a ready
     * to use destination url, otherwise this method will return <b>NULL</b>.
     *
     * @param string $url
     * @param \eZ\Publish\API\Repository\Values\Content\URLWildcard $wildcard
     *
     * @return string|null
     */
    private function match( $url, URLWildcard $wildcard )
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
     *
     * @return string
     */
    private function compile( $sourceUrl )
    {
        return '(^' . str_replace( '\\*', '(.*)', preg_quote( $sourceUrl ) ) . '$)U';
    }

    /**
     * Substitutes all placesholder ({\d}) in the given <b>$destinationUrl</b> with
     * the values from the given <b>$values</b> array.
     *
     * @param string $destinationUrl
     * @param array $values
     *
     * @throws \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\ContentValidationExceptionStub
     *
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
}
