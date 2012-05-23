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

use \eZ\Publish\API\Repository\Values\Content\UrlWildcard;

/**
 * Url wold service stub implementation.
 *
 * @package eZ\Publish\API\Repository\Tests\Stubs
 */
class URLWildcardServiceStub implements URLWildcardService
{
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
     */
    public function create( $sourceUrl, $destinationUrl, $foreward = false )
    {
        // TODO: Implement create() method.
    }

    /**
     *
     * removes an url wildcard
     *
     * @param \eZ\Publish\API\Repository\Values\Content\UrlWildcard $urlWildcard
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
     */
    public function translate( $url )
    {
        // TODO: Implement translate() method.
    }

}