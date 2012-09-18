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
use \eZ\Publish\API\Repository\LocationService;
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
     * Location service
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

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
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\API\Repository\SectionService $sectionService
     */
    public function __construct( Input\Dispatcher $inputDispatcher, UrlHandler $urlHandler, ContentService $contentService, LocationService $locationService, SectionService $sectionService )
    {
        $this->inputDispatcher = $inputDispatcher;
        $this->urlHandler      = $urlHandler;
        $this->contentService  = $contentService;
        $this->locationService = $locationService;
        $this->sectionService  = $sectionService;
    }

    /**
     * Load a content info by remote ID
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ContentList
     */
    public function loadContentInfoByRemoteId( RMF\Request $request )
    {
        $contentInfo = $this->contentService->loadContentInfoByRemoteId(
            // GET variable
            $request->variables['remoteId']
        );

        return new Values\ContentList(
            array(
                new Values\RestContent(
                    $contentInfo,
                    $this->locationService->loadLocation( $contentInfo->mainLocationId )
                )
            )
        );
    }

    /**
     * Loads a content info, potentially with the current version embedded
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\RestContent
     */
    public function loadContent( RMF\Request $request )
    {
        $urlValues = $this->urlHandler->parse( 'object', $request->path );

        $contentInfo = $this->contentService->loadContentInfo( $urlValues['object'] );
        $mainLocation = $this->locationService->loadLocation( $contentInfo->mainLocationId );

        $contentVersion = null;
        if ( $this->getMediaType( $request ) === 'application/vnd.ez.api.content' )
        {
            $contentVersion = $this->contentService->loadContent( $urlValues['object'] );
        }

        return new Values\RestContent( $contentInfo, $mainLocation, $contentVersion );
    }

    /**
     * Performs an update on the content meta data.
     *
     * @param RMF\Request $request
     * @return void
     */
    public function updateContentMetadata( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'object', $request->path );
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
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceRedirect
     */
    public function redirectCurrentVersion( RMF\Request $request )
    {
        $urlValues = $this->urlHandler->parse( 'objectCurrentVersion', $request->path );

        $contentInfo = $this->contentService->loadContentInfo( $urlValues['object'] );

        return new Values\ResourceRedirect(
            $this->urlHandler->generate(
                'objectVersion',
                array(
                    'object' => $urlValues['object'],
                    'version' => $contentInfo->currentVersionNo
                )
            ),
            'Version'
        );
    }

    /**
     * Loads a specific version of a given content object
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\Content\Content
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
     * Creates a new content draft assigned to the authenticated user.
     * If a different userId is given in the input it is assigned to the
     * given user but this required special rights for the authenticated
     * user (this is useful for content staging where the transfer process
     * does not have to authenticate with the user which created the content
     * object in the source server). The user has to publish the content if
     * it should be visible.
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedContent
     */
    public function createContent( RMF\Request $request )
    {
        $contentCreate = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
            )
        );

        $content = $this->contentService->createContent(
            $contentCreate->contentCreateStruct,
            array( $contentCreate->locationCreateStruct )
        );

        return new Values\CreatedContent(
            array(
                'content' => new Values\RestContent(
                    $content->contentInfo,
                    null,
                    $this->getMediaType( $request ) === 'application/vnd.ez.api.content' ? $content : null
                )
            )
        );
    }

    /**
     * The content is deleted. If the content has locations (which is required in 4.x)
     * on delete all locations assigned the content object are deleted via delete subtree.
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
     */
    public function deleteContent( RMF\Request $request )
    {
        $urlValues = $this->urlHandler->parse( 'object', $request->path );

        $this->contentService->deleteContent(
            $this->contentService->loadContentInfo( $urlValues['object'] )
        );

        return new Values\ResourceDeleted();
    }

    /**
     * Creates a new content object as copy under the given parent location given in the destination header.
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function copyContent( RMF\Request $request )
    {
        $urlValues = $this->urlHandler->parse( 'object', $request->path );
        $destinationValues = $this->urlHandler->parse( 'location', $request->destination );

        $parentLocationParts = explode( '/', $destinationValues['location'] );
        $copiedContent = $this->contentService->copyContent(
            $this->contentService->loadContentInfo( $urlValues['object'] ),
            $this->locationService->newLocationCreateStruct( array_pop( $parentLocationParts ) )
        );

        return new Values\ResourceCreated(
            $this->urlHandler->generate(
                'object',
                array( 'object' => $copiedContent->id )
            )
        );
    }

    /**
     * Returns a list of all versions of the content. This method does not
     * include fields and relations in the Version elements of the response.
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\VersionList
     */
    public function loadContentVersions( RMF\Request $request )
    {
        $urlValues = $this->urlHandler->parse( 'objectVersions', $request->path );

        return new Values\VersionList(
            $this->contentService->loadVersions(
                $this->contentService->loadContentInfo( $urlValues['object'] )
            ),
            $urlValues['object']
        );
    }

    /**
     * The version is deleted
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
     */
    public function deleteContentVersion( RMF\Request $request )
    {
        $urlValues = $this->urlHandler->parse( 'objectVersion', $request->path );

        $this->contentService->deleteVersion(
            $this->contentService->loadVersionInfo(
                $this->contentService->loadContentInfo( $urlValues['object'] ),
                $urlValues['version']
            )
        );

        return new Values\ResourceDeleted();
    }

    /**
     * The system creates a new draft version as a copy from the given version
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function createDraftFromVersion( RMF\Request $request )
    {
        $urlValues = $this->urlHandler->parse( 'objectVersion', $request->path );

        $contentInfo = $this->contentService->loadContentInfo( $urlValues['object'] );
        $contentDraft = $this->contentService->createContentDraft(
            $contentInfo,
            $this->contentService->loadVersionInfo(
                $contentInfo, $urlValues['version']
            )
        );

        return new Values\CreatedVersion(
            array(
                'version' => new Values\Version(
                    $contentDraft->versionInfo,
                    $urlValues['object']
                )
            )
        );
    }

    /**
     * The system creates a new draft version as a copy from the current version
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function createDraftFromCurrentVersion( RMF\Request $request )
    {
        $urlValues = $this->urlHandler->parse( 'objectVersions', $request->path );

        $contentInfo = $this->contentService->loadContentInfo( $urlValues['object'] );
        $contentDraft = $this->contentService->createContentDraft( $contentInfo );

        return new Values\CreatedVersion(
            array(
                'version' => new Values\Version(
                    $contentDraft->versionInfo,
                    $urlValues['object']
                )
            )
        );
    }

    /**
     * The content version is published
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function publishVersion( RMF\Request $request )
    {
        $urlValues = $this->urlHandler->parse( 'objectVersion', $request->path );

        $this->contentService->publishVersion(
            $this->contentService->loadVersionInfo(
                $this->contentService->loadContentInfo( $urlValues['object'] ),
                $urlValues['version']
            )
        );

        return new Values\NoContent();
    }

    /**
     * Extracts the requested media type from $request
     *
     * @param RMF\Request $request
     * @return string
     */
    protected function getMediaType( RMF\Request $request )
    {
        foreach ( $request->mimetype as $mimeType )
        {
            if ( preg_match( '(^([a-z0-9-/.]+)\+.*$)', $mimeType['value'], $matches ) )
            {
                return $matches[1];
            }
        }
        return 'unknown/unknown';
    }
}
