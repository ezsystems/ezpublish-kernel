<?php
/**
 * File containing the APIExceptionEvent class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Event;

use Symfony\Component\EventDispatcher\Event;
use eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface;
use Exception;

/**
 * This event is dispatched when an Exception from eZ Publish API is thrown and could not be caught before.
 * It allows you to handle this exception and affect a specific Response for it.
 */
class APIContentExceptionEvent extends Event
{
    /**
     * @var \Exception
     */
    private $apiException;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ContentView
     */
    private $contentView;

    /**
     * @var array
     */
    private $contentMeta;

    public function __construct( Exception $apiException, array $contentMeta )
    {
        $this->apiException = $apiException;
        $this->contentMeta = $contentMeta;
    }

    /**
     * @return \Exception
     */
    public function getApiException()
    {
        return $this->apiException;
    }

    /**
     * Injects the ContentView object to display content from.
     * It is a good idea to call {@link stopPropagation()} after that so that other listeners won't override it.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface $contentView
     */
    public function setContentView( ContentViewInterface $contentView )
    {
        $this->contentView = $contentView;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView
     */
    public function getContentView()
    {
        return $this->contentView;
    }

    /**
     * @return boolean
     */
    public function hasContentView()
    {
        return isset( $this->contentView );
    }

    /**
     * Returns an array of metadata concerning the content that failed to load through API.
     * This array includes:
     *  - contentId Content Id when applicable (not available if a location was looked up)
     *  - locationId Location Id when applicable (not available if a content was looked up)
     *  - viewType full/line/...
     *
     * @return array
     */
    public function getContentMeta()
    {
        return $this->contentMeta;
    }
}
