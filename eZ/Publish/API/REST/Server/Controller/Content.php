<?php
/**
 * File containing the Content controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Controller;
use eZ\Publish\API\REST\Common\Message;
use eZ\Publish\API\REST\Common\Input;
use eZ\Publish\API\REST\Server\Values;

use \eZ\Publish\API\Repository\ContentService;
use \eZ\Publish\API\Repository\SectionService;
use \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;

use Qafoo\RMF;

/**
 * Content controller
 */
class Content
{
    /**
     * Input dispatcher
     *
     * @var \eZ\Publish\API\REST\Server\InputDispatcher
     */
    protected $inputDispatcher;

    /**
     * Content service
     *
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * Construct controller
     *
     * @param Input\Dispatcher $inputDispatcher
     * @param ContentService $contentService
     * @param SectionService $sectionService
     * @return void
     */
    public function __construct( Input\Dispatcher $inputDispatcher, ContentService $contentService, SectionService $sectionService )
    {
        $this->inputDispatcher = $inputDispatcher;
        $this->contentService  = $contentService;
        $this->sectionService  = $sectionService;
    }

    /**
     * Load a content infor by remote ID
     *
     * @param RMF\Request $request
     * @return Content
     */
    public function loadContentInfoByRemoteId( RMF\Request $request )
    {
        return new Values\ContentList( array(
            $this->contentService->loadContentInfoByRemoteId(
                $request->variables['id']
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
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
            )
        );

        $contentInfo = $this->contentService->loadContentInfo(
            $request->variables['id']
        );

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
}
