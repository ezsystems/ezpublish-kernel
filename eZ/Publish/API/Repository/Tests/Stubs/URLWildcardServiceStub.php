<?php
/**
 * File containing the URLWildcardServiceStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use \eZ\Publish\API\Repository\URLWildcardService;
use \eZ\Publish\API\Repository\Values\Content\URLWildcard;

/**
 * Url wold service stub implementation.
 *
 * @package eZ\Publish\API\Repository\Tests\Stubs
 */
class URLWildcardServiceStub implements URLWildcardService
{
    /**
     * @var integer
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
     * creates a new url wildcard
     *
     * @param string $sourceUrl
     * @param string $destinationUrl
     * @param boolean $foreward
     *
     * @return \eZ\Publish\API\Repository\Values\Content\UrlWildcard
     * @review The method URLWildcardService::create() should check permissions and throw an AuthorizationException
     * @review The method URLWildcardService::create() should throw an InvalidArgumentException if the $sourceUrl pattern already exists
     * @review The method URLWildcardService::create() should throw a ValidationException if the number of * patterns in $sourceUrl and the number of {\d} placeholders in $destinationUrl doesn't match.
     * @review The method URLWildcardService::create() should throw a ValidationException if the placeholders aren't a valid number sequence({1}/{2}/{3}), starting with 1.
     */
    public function create( $sourceUrl, $destinationUrl, $foreward = false )
    {
        $wildcard = new URLWildcard(
            array(
                'id'  =>  ++$this->id,
                'sourceUrl'  =>  $sourceUrl,
                'destinationUrl'  =>  $destinationUrl,
                'forward'  =>  $foreward
            )
        );

        return ( $this->wildcards[$wildcard->id] = $wildcard );
    }

    /**
     *
     * removes an url wildcard
     *
     * @param \eZ\Publish\API\Repository\Values\Content\UrlWildcard $urlWildcard
     * @review The method URLWildcardService::remove() should check permissions and throw an AuthorizationException.
     * @review The real method type hint for the $urlWildcard parameter of URLWildcardService::remove() is missing.
     */
    public function remove( $urlWildcard )
    {
        // TODO: Implement remove() method.
    }

    /**
     *
     * loads a url wild card
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url wild card was not found
     *
     * @param $id
     *
     * @return \eZ\Publish\API\Repository\Values\Content\UrlWildcard
     * @review The type hint for the $id parameter of URLWildcardService::remove() is missing.
     */
    public function load( $id )
    {
        // TODO: Implement load() method.
    }

    /**
     * loads all url wild card (paged)
     *
     * @param $offset
     * @param $limit
     *
     * @return \eZ\Publish\API\Repository\Values\Content\UrlWildcard[]
     * @review The type hint for the $offset parameter of URLWildcardService::loadAll() is missing
     * @review The type hint for the $limit parameter of URLWildcardService::loadAll() is missing
     */
    public function loadAll( $offset = 0, $limit = -1 )
    {
        // TODO: Implement loadAll() method.
    }

    /**
     * translates an url to an existing uri resource or url alias based on the source/destination patterns of the url wildcard.
     * this method runs also configured url translations and filter
     *
     * @param $url
     *
     * @return mixed either an URLAlias or a URLWildcardTranslationResult
     * @review The type hint for the $url parameter of URLWildcardService::translate() is missing
     */
    public function translate( $url )
    {
        // TODO: Implement translate() method.
    }

}