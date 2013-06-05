<?php
/**
 * URLWildcardService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\RequestCache;

use eZ\Publish\API\Repository\URLWildcardService as URLWildcardServiceInterface;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;

/**
 * URLWildcardService class
 * @package eZ\Publish\Core\RequestCache
 */
class URLWildcardService implements URLWildcardServiceInterface
{
    /**
     * Aggregated service
     *
     * @var \eZ\Publish\API\Repository\URLWildcardService
     */
    protected $service;

    /**
     * CachePool
     *
     * @var \eZ\Publish\Core\RequestCache\CachePool
     */
    protected $cachePool;

    /**
     * Constructor
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\URLWildcardService $service
     * @param \eZ\Publish\Core\RequestCache\CachePool $cachePool
     */
    public function __construct( URLWildcardServiceInterface $service, CachePool $cachePool )
    {
        $this->service = $service;
        $this->cachePool = $cachePool;
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
        return $this->service->create( $sourceUrl, $destinationUrl, $forward );
    }

    /**
     * removes an url wildcard
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to remove url wildcards
     *
     * @param \eZ\Publish\API\Repository\Values\Content\URLWildcard $urlWildcard the url wildcard to remove
     */
    public function remove( URLWildcard $urlWildcard )
    {
        return $this->service->remove( $urlWildcard );
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
        return $this->service->load( $id );
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
        return $this->service->loadAll( $offset, $limit );
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
        return $this->service->translate( $url );
    }
}
