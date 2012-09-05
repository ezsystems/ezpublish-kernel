<?php
/**
 * File containing the Content controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Input;
use eZ\Publish\Core\REST\Server\Values;

use \eZ\Publish\API\Repository\ContentService;
use \eZ\Publish\API\Repository\SectionService;

use Qafoo\RMF;

/**
 * Content controller
 */
class Content
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
     * Content service
     *
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * Section service
     *
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $sectionService;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\SectionService $sectionService
     */
    public function __construct( Input\Dispatcher $inputDispatcher, UrlHandler $urlHandler, ContentService $contentService, SectionService $sectionService )
    {
        $this->inputDispatcher = $inputDispatcher;
        $this->urlHandler      = $urlHandler;
        $this->contentService  = $contentService;
        $this->sectionService  = $sectionService;
    }

    /**
     * Load a content info by remote ID
     *
     * @param RMF\Request $request
     * @return Content
     */
    public function loadContentInfoByRemoteId( RMF\Request $request )
    {
        return new Values\ContentList( array(
            $this->contentService->loadContentInfoByRemoteId(
                // GET variable
                $request->variables['remoteId']
            )
        ) );
    }

    /**
     * Performs an update on the content meta data.
     *
     * @param RMF\Request $request
     * @return void
     */
    public function updateContentMetadata( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'content', $request->path );
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
            )
        );

        $contentInfo = $this->contentService->loadContentInfo( $values['object'] );

        if ( $updateStruct->sectionId !== null )
        {
            $section = $this->sectionService->loadSection( $updateStruct->sectionId );
            $this->sectionService->assignSection( $contentInfo, $section );
        }

        /*
         * TODO: Implement visitor.
        return $this->contentService->updateContentMetadata(
            $contentInfo,
            $updateStruct
        );
        */
        // Since by now only used for section assign, we return null
        return null;
    }

    /**
     * Loads a specific version of a given content object
     *
     * @param RMF\Request $request
     * @return void
     */
    public function loadContentInVersion( RMF\Request $request )
    {
        $urlValues = $this->urlHandler->parse( 'objectVersion', $request->path );

        return $this->contentService->loadContent(
            $urlValues['object'],
            null,               // TODO: Implement using language filter on request URI
            $urlValues['version']
        );
    }

    /**
     * Loads a specific version of a given content object
     *
     * @param RMF\Request $request
     * @return void
     * @todo Fix this to return a redirect to the actual version URI!
     */
    public function loadContentInCurrentVersion( RMF\Request $request )
    {
        $urlValues = $this->urlHandler->parse( 'objectCurrentVersion', $request->path );

        return $this->contentService->loadContent(
            $urlValues['object'],
            null                // TODO: Implement using language filter on request URI
        );
    }
}
