<?php
/**
 * File containing the URLAlias controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Input;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Server\Controller as RestController;

use eZ\Publish\API\Repository\URLAliasService;

/**
 * URLAlias controller
 */
class URLAlias extends RestController
{
    /**
     * URLAlias service
     *
     * @var \eZ\Publish\API\Repository\URLAliasService
     */
    protected $urlAliasService;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\API\Repository\URLAliasService $urlAliasService
     */
    public function __construct( URLAliasService $urlAliasService )
    {
        $this->urlAliasService = $urlAliasService;
    }

    /**
     * Returns the URL alias with the given ID
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function loadURLAlias()
    {
        $urlValues = $this->urlHandler->parse( 'urlAlias', $this->request->path );
        return $this->urlAliasService->load( $urlValues['urlalias'] );
    }

    /**
     * Returns a list of URL aliases
     *
     * @return \eZ\Publish\Core\REST\Server\Values\URLAliasList
     */
    public function listURLAliases()
    {
    }

    /**
     * Creates a new URL alias
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedURLAlias
     */
    public function createURLAlias()
    {
    }

    /**
     * The given URL alias is deleted
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteURLAlias()
    {
        $urlValues = $this->urlHandler->parse( 'urlAlias', $this->request->path );

        $this->urlAliasService->removeAliases(
            array(
                $this->urlAliasService->load( $urlValues['urlalias'] )
            )
        );

        return new Values\NoContent();
    }
}
