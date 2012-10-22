<?php
/**
 * URLWildcardService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot;
use \eZ\Publish\API\Repository\URLWildcardService as URLWildcardServiceInterface;

/**
 * URLWildcardService class
 * @package eZ\Publish\Core\SignalSlot
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
     * SignalDispatcher
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\URLWildcardService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct( URLWildcardServiceInterface $service, SignalDispatcher $signalDispatcher )
    {
        $this->service          = $service;
        $this->signalDispatcher = $signalDispatcher;
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
     * @param boolean $foreward
     *
     * @return \eZ\Publish\API\Repository\Values\Content\UrlWildcard
     */
    public function create( $sourceUrl, $destinationUrl, $foreward = false )
    {
        $returnValue = $this->service->create( $sourceUrl, $destinationUrl, $foreward );
        $this->signalDispatcher->emit(
            new Signal\URLWildcardService\CreateSignal( array(
                'urlWildcardId' => $returnValue->id,
            ) )
        );
        return $returnValue;
    }

    /**
     * removes an url wildcard
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to remove url wildcards
     *
     * @param \eZ\Publish\API\Repository\Values\Content\UrlWildcard $urlWildcard the url wildcard to remove
     */
    public function remove( \eZ\Publish\API\Repository\Values\Content\URLWildcard $urlWildcard )
    {
        $returnValue = $this->service->remove( $urlWildcard );
        $this->signalDispatcher->emit(
            new Signal\URLWildcardService\RemoveSignal( array(
                'urlWildcardId' => $urlWildcard->id,
            ) )
        );
        return $returnValue;
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
        $returnValue = $this->service->load( $id );
        return $returnValue;
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
        $returnValue = $this->service->loadAll( $offset, $limit );
        return $returnValue;
    }

    /**
     * translates an url to an existing uri resource based on the
     * source/destination patterns of the url wildcard. If the resulting
     * url is an alias it will be transltated to the system uri.
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
        $returnValue = $this->service->translate( $url );
        $this->signalDispatcher->emit(
            new Signal\URLWildcardService\TranslateSignal( array(
                'url' => $url,
            ) )
        );
        return $returnValue;
    }

}

