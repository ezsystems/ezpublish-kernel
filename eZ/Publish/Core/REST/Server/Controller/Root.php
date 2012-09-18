<?php
/**
 * File containing the Root controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Input;
use eZ\Publish\Core\REST\Common\Values;

use Qafoo\RMF;

/**
 * Root controller
 */
class Root
{
    /**
     * Input dispatcher
     *
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    protected $inputDispatcher;

    /**
     * URL handler
     *
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    protected $urlHandler;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     */
    public function __construct( Input\Dispatcher $inputDispatcher, UrlHandler $urlHandler )
    {
        $this->inputDispatcher = $inputDispatcher;
        $this->urlHandler      = $urlHandler;
    }

    /**
     * List the root resources of the eZ Publish installation
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Common\Values\Root
     */
    public function loadRootResource( RMF\Request $request )
    {
        return new Values\Root();
    }
}
