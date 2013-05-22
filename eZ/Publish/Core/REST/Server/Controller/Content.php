<?php
/**
 * File containing the Content controller class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Input;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Server\Controller as RestController;

use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException;
use eZ\Publish\API\Repository\Exceptions\ContentValidationException;
use eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException;
use eZ\Publish\Core\REST\Server\Exceptions\BadRequestException;

/**
 * Content controller
 */
class Content extends RestController
{
    /**
     * Loads a content info by remote ID
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\BadRequestException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\TemporaryRedirect
     */
    public function redirectContent()
    {
        if ( !isset( $this->request->variables['remoteId'] ) )
        {
            throw new BadRequestException( "'remoteId' parameter is required." );
        }

        $contentInfo = $this->repository->getContentService()->loadContentInfoByRemoteId( $this->request->variables['remoteId'] );

        return new Values\TemporaryRedirect(
            $this->urlHandler->generate(
                'object',
                array(
                    'object' => $contentInfo->id
                )
            )
        );
    }

    /**
     * Loads a content info, potentially with the current version embedded
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestContent
     */
    public function loadContent()
    {
        $questionMark = strpos( $this->request->path, '?' );
        $requestPath = $questionMark !== false ? substr( $this->request->path, 0, $questionMark ) : $this->request->path;

        $urlValues = $this->urlHandler->parse( 'object', $requestPath );

        $contentInfo = $this->repository->getContentService()->loadContentInfo( $urlValues['object'] );
        $mainLocation = $this->repository->getLocationService()->loadLocation( $contentInfo->mainLocationId );
        $contentType = $this->repository->getContentTypeService()->loadContentType( $contentInfo->contentTypeId );

        $contentVersion = null;
        $relations = null;
        if ( $this->getMediaType( $this->request ) === 'application/vnd.ez.api.content' )
        {
            $languages = null;
            if ( isset( $this->request->variables['languages'] ) )
            {
                $languages = explode( ',', $this->request->variables['languages'] );
            }

            $contentVersion = $this->repository->getContentService()->loadContent( $urlValues['object'], $languages );
            $relations = $this->repository->getContentService()->loadRelations( $contentVersion->getVersionInfo() );
        }

        return new Values\RestContent(
            $contentInfo,
            $mainLocation,
            $contentVersion,
            $contentType,
            $relations,
            $this->request->path
        );
    }

    /**
     * Updates a content's metadata
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestContent
     */
    public function updateContentMetadata()
    {
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        $values = $this->urlHandler->parse( 'object', $this->request->path );
        $contentId = (int)$values['object'];
        $contentInfo = $this->repository->getContentService()->loadContentInfo( $contentId );

        // update section
        if ( $updateStruct->sectionId !== null )
        {
            $section = $this->repository->getSectionService->loadSection( $updateStruct->sectionId );
            $this->repository->getSectionService->assignSection( $contentInfo, $section );
            $updateStruct->sectionId = null;
        }

        // @todo Consider refactoring! ContentService::updateContentMetadata throws the same exception
        // in case the updateStruct is empty and if remoteId already exists. Since REST version of update struct
        // includes section ID in addition to other fields, we cannot throw exception if only sectionId property
        // is set, so we must skip updating content in that case instead of allowing propagation of the exception.
        foreach ( $updateStruct as $propertyName => $propertyValue )
        {
            if ( $propertyName !== 'sectionId' && $propertyValue !== null )
            {
                // update content
                $this->repository->getContentService()->updateContentMetadata( $contentInfo, $updateStruct );
                $contentInfo = $this->repository->getContentService()->loadContentInfo( $contentId );
                break;
            }
        }

        try
        {
            $locationInfo = $this->repository->getLocationService()->loadLocation( $contentInfo->mainLocationId );
        }
        catch ( NotFoundException $e )
        {
            $locationInfo = null;
        }

        return new Values\RestContent(
            $contentInfo,
            $locationInfo
        );
    }

    /**
     * Loads a specific version of a given content object
     *
     * @return \eZ\Publish\Core\REST\Server\Values\TemporaryRedirect
     */
    public function redirectCurrentVersion()
    {
        $urlValues = $this->urlHandler->parse( 'objectCurrentVersion', $this->request->path );

        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo( $urlValues['object'] )
        );

        return new Values\TemporaryRedirect(
            $this->urlHandler->generate(
                'objectVersion',
                array(
                    'object' => $urlValues['object'],
                    'version' => $versionInfo->versionNo
                )
            )
        );
    }

    /**
     * Loads a specific version of a given content object
     *
     * @return \eZ\Publish\Core\REST\Server\Values\Version
     */
    public function loadContentInVersion()
    {
        $questionMark = strpos( $this->request->path, '?' );
        $requestPath = $questionMark !== false ? substr( $this->request->path, 0, $questionMark ) : $this->request->path;

        $urlValues = $this->urlHandler->parse( 'objectVersion', $requestPath );

        $languages = null;
        if ( isset( $this->request->variables['languages'] ) )
        {
            $languages = explode( ',', $this->request->variables['languages'] );
        }

        $content = $this->repository->getContentService()->loadContent(
            $urlValues['object'],
            $languages,
            $urlValues['version']
        );
        $contentType = $this->repository->getContentTypeService()->loadContentType(
            $content->getVersionInfo()->getContentInfo()->contentTypeId
        );

        return new Values\Version(
            $content,
            $contentType,
            $this->repository->getContentService()->loadRelations( $content->getVersionInfo() ),
            $this->request->path
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
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedContent
     */
    public function createContent()
    {
        $contentCreate = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        try
        {
            $content = $this->repository->getContentService()->createContent(
                $contentCreate->contentCreateStruct,
                array( $contentCreate->locationCreateStruct )
            );
        }
        catch ( ContentValidationException $e )
        {
            throw new BadRequestException( $e->getMessage() );
        }
        catch ( ContentFieldValidationException $e )
        {
            throw new BadRequestException( $e->getMessage() );
        }

        $contentValue = null;
        $contentType = null;
        $relations = null;
        if ( $this->getMediaType( $this->request ) === 'application/vnd.ez.api.content' )
        {
            $contentValue = $content;
            $contentType = $this->repository->getContentTypeService()->loadContentType(
                $content->getVersionInfo()->getContentInfo()->contentTypeId
            );
            $relations = $this->repository->getContentService()->loadRelations( $contentValue->getVersionInfo() );
        }

        return new Values\CreatedContent(
            array(
                'content' => new Values\RestContent(
                    $content->contentInfo,
                    null,
                    $contentValue,
                    $contentType,
                    $relations
                )
            )
        );
    }

    /**
     * The content is deleted. If the content has locations (which is required in 4.x)
     * on delete all locations assigned the content object are deleted via delete subtree.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteContent()
    {
        $urlValues = $this->urlHandler->parse( 'object', $this->request->path );

        $this->repository->getContentService()->deleteContent(
            $this->repository->getContentService()->loadContentInfo( $urlValues['object'] )
        );

        return new Values\NoContent();
    }

    /**
     * Creates a new content object as copy under the given parent location given in the destination header.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function copyContent()
    {
        $urlValues = $this->urlHandler->parse( 'object', $this->request->path );
        $destinationValues = $this->urlHandler->parse( 'location', $this->request->destination );

        $parentLocationParts = explode( '/', $destinationValues['location'] );
        $copiedContent = $this->repository->getContentService()->copyContent(
            $this->repository->getContentService()->loadContentInfo( $urlValues['object'] ),
            $this->repository->getLocationService()->newLocationCreateStruct( array_pop( $parentLocationParts ) )
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
     * @return \eZ\Publish\Core\REST\Server\Values\VersionList
     */
    public function loadContentVersions()
    {
        $urlValues = $this->urlHandler->parse( 'objectVersions', $this->request->path );

        return new Values\VersionList(
            $this->repository->getContentService()->loadVersions(
                $this->repository->getContentService()->loadContentInfo( $urlValues['object'] )
            ),
            $this->request->path
        );
    }

    /**
     * The version is deleted
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteContentVersion()
    {
        $urlValues = $this->urlHandler->parse( 'objectVersion', $this->request->path );

        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo( $urlValues['object'] ),
            $urlValues['version']
        );

        if ( $versionInfo->status === VersionInfo::STATUS_PUBLISHED )
        {
            throw new ForbiddenException( 'Version in status PUBLISHED cannot be deleted' );
        }

        $this->repository->getContentService()->deleteVersion(
            $versionInfo
        );

        return new Values\NoContent();
    }

    /**
     * The system creates a new draft version as a copy from the given version
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedVersion
     */
    public function createDraftFromVersion()
    {
        $urlValues = $this->urlHandler->parse( 'objectVersion', $this->request->path );

        $contentInfo = $this->repository->getContentService()->loadContentInfo( $urlValues['object'] );
        $contentType = $this->repository->getContentTypeService()->loadContentType( $contentInfo->contentTypeId );
        $contentDraft = $this->repository->getContentService()->createContentDraft(
            $contentInfo,
            $this->repository->getContentService()->loadVersionInfo(
                $contentInfo, $urlValues['version']
            )
        );

        return new Values\CreatedVersion(
            array(
                'version' => new Values\Version(
                    $contentDraft,
                    $contentType,
                    $this->repository->getContentService()->loadRelations( $contentDraft->getVersionInfo() )
                )
            )
        );
    }

    /**
     * The system creates a new draft version as a copy from the current version
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedVersion
     */
    public function createDraftFromCurrentVersion()
    {
        $urlValues = $this->urlHandler->parse( 'objectCurrentVersion', $this->request->path );

        $contentInfo = $this->repository->getContentService()->loadContentInfo( $urlValues['object'] );
        $contentType = $this->repository->getContentTypeService()->loadContentType( $contentInfo->contentTypeId );
        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $contentInfo
        );

        if ( $versionInfo->status === VersionInfo::STATUS_DRAFT )
        {
            throw new ForbiddenException( 'Current version is already in status DRAFT' );
        }

        $contentDraft = $this->repository->getContentService()->createContentDraft( $contentInfo );

        return new Values\CreatedVersion(
            array(
                'version' => new Values\Version(
                    $contentDraft,
                    $contentType,
                    $this->repository->getContentService()->loadRelations( $contentDraft->getVersionInfo() )
                )
            )
        );
    }

    /**
     * A specific draft is updated.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\Version
     */
    public function updateVersion()
    {
        $questionMark = strpos( $this->request->path, '?' );
        $requestPath = $questionMark !== false ? substr( $this->request->path, 0, $questionMark ) : $this->request->path;

        $urlValues = $this->urlHandler->parse( 'objectVersion', $requestPath );

        $contentUpdateStruct = $this->inputDispatcher->parse(
            new Message(
                array(
                    'Content-Type' => $this->request->contentType,
                    // @todo Needs refactoring! Temporary solution so parser has access to URL
                    'Url' => $requestPath
                ),
                $this->request->body
            )
        );

        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo( $urlValues['object'] ),
            $urlValues['version']
        );

        if ( $versionInfo->status !== VersionInfo::STATUS_DRAFT )
        {
            throw new ForbiddenException( 'Only version in status DRAFT can be updated' );
        }

        try
        {
            $this->repository->getContentService()->updateContent( $versionInfo, $contentUpdateStruct );
        }
        catch ( ContentValidationException $e )
        {
            throw new BadRequestException( $e->getMessage() );
        }
        catch ( ContentFieldValidationException $e )
        {
            throw new BadRequestException( $e->getMessage() );
        }

        $languages = null;
        if ( isset( $this->request->variables['languages'] ) )
        {
            $languages = explode( ',', $this->request->variables['languages'] );
        }

        // Reload the content to handle languages GET parameter
        $content = $this->repository->getContentService()->loadContent(
            $urlValues['object'],
            $languages,
            $versionInfo->versionNo
        );
        $contentType = $this->repository->getContentTypeService()->loadContentType(
            $content->getVersionInfo()->getContentInfo()->contentTypeId
        );

        return new Values\Version(
            $content,
            $contentType,
            $this->repository->getContentService()->loadRelations( $content->getVersionInfo() ),
            $this->request->path
        );
    }

    /**
     * The content version is published
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function publishVersion()
    {
        $urlValues = $this->urlHandler->parse( 'objectVersion', $this->request->path );

        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo( $urlValues['object'] ),
            $urlValues['version']
        );

        if ( $versionInfo->status !== VersionInfo::STATUS_DRAFT )
        {
            throw new ForbiddenException( 'Only version in status DRAFT can be published' );
        }

        $this->repository->getContentService()->publishVersion(
            $versionInfo
        );

        return new Values\NoContent();
    }

    /**
     * Redirects to the relations of the current version
     *
     * @return \eZ\Publish\Core\REST\Server\Values\TemporaryRedirect
     */
    public function redirectCurrentVersionRelations()
    {
        $urlValues = $this->urlHandler->parse( 'objectrelations', $this->request->path );

        $contentInfo = $this->repository->getContentService()->loadContentInfo( $urlValues['object'] );
        return new Values\TemporaryRedirect(
            $this->urlHandler->generate(
                'objectVersionRelations',
                array(
                    'object' => $urlValues['object'],
                    'version' => $contentInfo->currentVersionNo
                )
            )
        );
    }

    /**
     * Loads the relations of the given version
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RelationList
     */
    public function loadVersionRelations()
    {
        $questionMark = strpos( $this->request->path, '?' );
        $requestPath = $questionMark !== false ? substr( $this->request->path, 0, $questionMark ) : $this->request->path;

        $urlValues = $this->urlHandler->parse( 'objectVersionRelations', $requestPath );

        $offset = isset( $this->request->variables['offset'] ) ? (int)$this->request->variables['offset'] : 0;
        $limit = isset( $this->request->variables['limit'] ) ? (int)$this->request->variables['limit'] : -1;

        $relationList = $this->repository->getContentService()->loadRelations(
            $this->repository->getContentService()->loadVersionInfo(
                $this->repository->getContentService()->loadContentInfo( $urlValues['object'] ),
                $urlValues['version']
            )
        );

        $relationList = array_slice(
            $relationList,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : null
        );

        return new Values\RelationList(
            $relationList,
            $urlValues['object'],
            $urlValues['version'],
            $this->request->path
        );
    }

    /**
     * Loads a relation for the given content object and version
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestRelation
     */
    public function loadVersionRelation()
    {
        $urlValues = $this->urlHandler->parse( 'objectVersionRelation', $this->request->path );

        $relationList = $this->repository->getContentService()->loadRelations(
            $this->repository->getContentService()->loadVersionInfo(
                $this->repository->getContentService()->loadContentInfo( $urlValues['object'] ),
                $urlValues['version']
            )
        );

        foreach ( $relationList as $relation )
        {
            if ( $relation->id == $urlValues['relation'] )
            {
                return new Values\RestRelation( $relation, $urlValues['object'], $urlValues['version'] );
            }
        }

        throw new Exceptions\NotFoundException( "Relation not found: '{$this->request->path}'." );
    }

    /**
     * Deletes a relation of the given draft.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function removeRelation()
    {
        $urlValues = $this->urlHandler->parse( 'objectVersionRelation', $this->request->path );

        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo( $urlValues['object'] ),
            $urlValues['version']
        );

        $versionRelations = $this->repository->getContentService()->loadRelations( $versionInfo );
        foreach ( $versionRelations as $relation )
        {
            if ( $relation->id == $urlValues['relation'] )
            {
                if ( $relation->type !== Relation::COMMON )
                {
                    throw new ForbiddenException( "Relation is not of type COMMON" );
                }

                if ( $versionInfo->status !== VersionInfo::STATUS_DRAFT )
                {
                    throw new ForbiddenException( "Relation of type COMMON can only be removed from drafts" );
                }

                $this->repository->getContentService()->deleteRelation( $versionInfo, $relation->getDestinationContentInfo() );
                return new Values\NoContent();
            }
        }

        throw new Exceptions\NotFoundException( "Relation not found: '{$this->request->path}'." );
    }

    /**
     * Creates a new relation of type COMMON for the given draft.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedRelation
     */
    public function createRelation()
    {
        $urlValues = $this->urlHandler->parse( 'objectVersionRelations', $this->request->path );
        $destinationContentId = $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        $contentInfo = $this->repository->getContentService()->loadContentInfo( $urlValues['object'] );
        $versionInfo = $this->repository->getContentService()->loadVersionInfo( $contentInfo, $urlValues['version'] );
        if ( $versionInfo->status !== VersionInfo::STATUS_DRAFT )
        {
            throw new ForbiddenException( "Relation of type COMMON can only be added to drafts" );
        }

        try
        {
            $destinationContentInfo = $this->repository->getContentService()->loadContentInfo( $destinationContentId );
        }
        catch ( NotFoundException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }

        $existingRelations = $this->repository->getContentService()->loadRelations( $versionInfo );
        foreach ( $existingRelations as $existingRelation )
        {
            if ( $existingRelation->getDestinationContentInfo()->id == $destinationContentId )
            {
                throw new ForbiddenException( "Relation of type COMMON to selected destination content ID already exists" );
            }
        }

        $relation = $this->repository->getContentService()->addRelation( $versionInfo, $destinationContentInfo );
        return new Values\CreatedRelation(
            array(
                'relation' => new Values\RestRelation( $relation, $urlValues['object'], $urlValues['version'] )
            )
        );
    }

    /**
     * Creates and executes a content view
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestExecutedView
     */
    public function createView()
    {
        $viewInput = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );
        return new Values\RestExecutedView(
            array(
                'identifier'    => $viewInput->identifier,
                'searchResults' => $this->repository->getSearchService()->findContent( $viewInput->query ),
            )
        );
    }

    /**
     * Extracts the requested media type from $request
     *
     * @return string
     */
    protected function getMediaType()
    {
        foreach ( $this->request->mimetype as $mimeType )
        {
            if ( preg_match( '(^([a-z0-9-/.]+)\+.*$)', $mimeType['value'], $matches ) )
            {
                return $matches[1];
            }
        }
        return 'unknown/unknown';
    }
}
