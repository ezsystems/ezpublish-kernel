<?php

/**
 * File containing the Content controller class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\Core\REST\Common\Message;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Content controller.
 */
class Content extends RestController
{
    /**
     * Loads a content info by remote ID.
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\BadRequestException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\TemporaryRedirect
     */
    public function redirectContent(Request $request)
    {
        if (!$request->query->has('remoteId')) {
            throw new BadRequestException("'remoteId' parameter is required.");
        }

        $contentInfo = $this->repository->getContentService()->loadContentInfoByRemoteId(
            $request->query->get('remoteId')
        );

        return new Values\TemporaryRedirect(
            $this->router->generate(
                'ezpublish_rest_loadContent',
                array(
                    'contentId' => $contentInfo->id,
                )
            )
        );
    }

    /**
     * Loads a content info, potentially with the current version embedded.
     *
     * @param mixed $contentId
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestContent
     */
    public function loadContent($contentId, Request $request)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        $mainLocation = null;
        if (!empty($contentInfo->mainLocationId)) {
            $mainLocation = $this->repository->getLocationService()->loadLocation($contentInfo->mainLocationId);
        }

        $contentType = $this->repository->getContentTypeService()->loadContentType($contentInfo->contentTypeId);

        $contentVersion = null;
        $relations = null;
        if ($this->getMediaType($request) === 'application/vnd.ez.api.content') {
            $languages = null;
            if ($request->query->has('languages')) {
                $languages = explode(',', $request->query->get('languages'));
            }

            $contentVersion = $this->repository->getContentService()->loadContent($contentId, $languages);
            $relations = $this->repository->getContentService()->loadRelations($contentVersion->getVersionInfo());
        }

        $restContent = new Values\RestContent(
            $contentInfo,
            $mainLocation,
            $contentVersion,
            $contentType,
            $relations,
            $request->getPathInfo()
        );

        if ($contentInfo->mainLocationId === null) {
            return $restContent;
        }

        return new Values\CachedValue(
            $restContent,
            array('locationId' => $contentInfo->mainLocationId)
        );
    }

    /**
     * Updates a content's metadata.
     *
     * @param mixed $contentId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestContent
     */
    public function updateContentMetadata($contentId, Request $request)
    {
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array('Content-Type' => $request->headers->get('Content-Type')),
                $request->getContent()
            )
        );

        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        // update section
        if ($updateStruct->sectionId !== null) {
            $section = $this->repository->getSectionService()->loadSection($updateStruct->sectionId);
            $this->repository->getSectionService()->assignSection($contentInfo, $section);
            $updateStruct->sectionId = null;
        }

        // @todo Consider refactoring! ContentService::updateContentMetadata throws the same exception
        // in case the updateStruct is empty and if remoteId already exists. Since REST version of update struct
        // includes section ID in addition to other fields, we cannot throw exception if only sectionId property
        // is set, so we must skip updating content in that case instead of allowing propagation of the exception.
        foreach ($updateStruct as $propertyName => $propertyValue) {
            if ($propertyName !== 'sectionId' && $propertyValue !== null) {
                // update content
                $this->repository->getContentService()->updateContentMetadata($contentInfo, $updateStruct);
                $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
                break;
            }
        }

        try {
            $locationInfo = $this->repository->getLocationService()->loadLocation($contentInfo->mainLocationId);
        } catch (NotFoundException $e) {
            $locationInfo = null;
        }

        return new Values\RestContent(
            $contentInfo,
            $locationInfo
        );
    }

    /**
     * Loads a specific version of a given content object.
     *
     * @param mixed $contentId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\TemporaryRedirect
     */
    public function redirectCurrentVersion($contentId)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        return new Values\TemporaryRedirect(
            $this->router->generate(
                'ezpublish_rest_loadContentInVersion',
                array(
                    'contentId' => $contentId,
                    'versionNumber' => $contentInfo->currentVersionNo,
                )
            )
        );
    }

    /**
     * Loads a specific version of a given content object.
     *
     * @param mixed $contentId
     * @param int $versionNumber
     *
     * @return \eZ\Publish\Core\REST\Server\Values\Version
     */
    public function loadContentInVersion($contentId, $versionNumber, Request $request)
    {
        $languages = null;
        if ($request->query->has('languages')) {
            $languages = explode(',', $request->query->get('languages'));
        }

        $content = $this->repository->getContentService()->loadContent(
            $contentId,
            $languages,
            $versionNumber
        );
        $contentType = $this->repository->getContentTypeService()->loadContentType(
            $content->getVersionInfo()->getContentInfo()->contentTypeId
        );

        $versionValue = new Values\Version(
            $content,
            $contentType,
            $this->repository->getContentService()->loadRelations($content->getVersionInfo()),
            $request->getPathInfo()
        );

        if ($content->contentInfo->mainLocationId === null) {
            return $versionValue;
        }

        return new Values\CachedValue(
            $versionValue,
            array('locationId' => $content->contentInfo->mainLocationId)
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
    public function createContent(Request $request)
    {
        $contentCreate = $this->inputDispatcher->parse(
            new Message(
                array('Content-Type' => $request->headers->get('Content-Type')),
                $request->getContent()
            )
        );

        try {
            $content = $this->repository->getContentService()->createContent(
                $contentCreate->contentCreateStruct,
                array($contentCreate->locationCreateStruct)
            );
        } catch (ContentValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (ContentFieldValidationException $e) {
            throw new BadRequestException($e->getMessage());
        }

        $contentValue = null;
        $contentType = null;
        $relations = null;
        if ($this->getMediaType($request) === 'application/vnd.ez.api.content') {
            $contentValue = $content;
            $contentType = $this->repository->getContentTypeService()->loadContentType(
                $content->getVersionInfo()->getContentInfo()->contentTypeId
            );
            $relations = $this->repository->getContentService()->loadRelations($contentValue->getVersionInfo());
        }

        return new Values\CreatedContent(
            array(
                'content' => new Values\RestContent(
                    $content->contentInfo,
                    null,
                    $contentValue,
                    $contentType,
                    $relations
                ),
            )
        );
    }

    /**
     * The content is deleted. If the content has locations (which is required in 4.x)
     * on delete all locations assigned the content object are deleted via delete subtree.
     *
     * @param mixed $contentId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteContent($contentId)
    {
        $this->repository->getContentService()->deleteContent(
            $this->repository->getContentService()->loadContentInfo($contentId)
        );

        return new Values\NoContent();
    }

    /**
     * Creates a new content object as copy under the given parent location given in the destination header.
     *
     * @param mixed $contentId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function copyContent($contentId, Request $request)
    {
        $destination = $request->headers->get('Destination');

        $parentLocationParts = explode('/', $destination);
        $copiedContent = $this->repository->getContentService()->copyContent(
            $this->repository->getContentService()->loadContentInfo($contentId),
            $this->repository->getLocationService()->newLocationCreateStruct(array_pop($parentLocationParts))
        );

        return new Values\ResourceCreated(
            $this->router->generate(
                'ezpublish_rest_loadContent',
                array('contentId' => $copiedContent->id)
            )
        );
    }

    /**
     * Returns a list of all versions of the content. This method does not
     * include fields and relations in the Version elements of the response.
     *
     * @param mixed $contentId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\VersionList
     */
    public function loadContentVersions($contentId, Request $request)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        $versionList = new Values\VersionList(
            $this->repository->getContentService()->loadVersions($contentInfo),
            $request->getPathInfo()
        );

        if ($contentInfo->mainLocationId === null) {
            return $versionList;
        }

        return new Values\CachedValue(
            $versionList,
            array('locationId' => $contentInfo->mainLocationId)
        );
    }

    /**
     * The version is deleted.
     *
     * @param mixed $contentId
     * @param mixed $versionNumber
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteContentVersion($contentId, $versionNumber)
    {
        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo($contentId),
            $versionNumber
        );

        if ($versionInfo->status === VersionInfo::STATUS_PUBLISHED) {
            throw new ForbiddenException('Version in status PUBLISHED cannot be deleted');
        }

        $this->repository->getContentService()->deleteVersion(
            $versionInfo
        );

        return new Values\NoContent();
    }

    /**
     * The system creates a new draft version as a copy from the given version.
     *
     * @param mixed $contentId
     * @param mixed $versionNumber
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedVersion
     */
    public function createDraftFromVersion($contentId, $versionNumber)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $contentType = $this->repository->getContentTypeService()->loadContentType($contentInfo->contentTypeId);
        $contentDraft = $this->repository->getContentService()->createContentDraft(
            $contentInfo,
            $this->repository->getContentService()->loadVersionInfo($contentInfo, $versionNumber)
        );

        return new Values\CreatedVersion(
            array(
                'version' => new Values\Version(
                    $contentDraft,
                    $contentType,
                    $this->repository->getContentService()->loadRelations($contentDraft->getVersionInfo())
                ),
            )
        );
    }

    /**
     * The system creates a new draft version as a copy from the current version.
     *
     * @param mixed $contentId
     *
     * @throws ForbiddenException if the current version is already a draft
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedVersion
     */
    public function createDraftFromCurrentVersion($contentId)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $contentType = $this->repository->getContentTypeService()->loadContentType($contentInfo->contentTypeId);
        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $contentInfo
        );

        if ($versionInfo->status === VersionInfo::STATUS_DRAFT) {
            throw new ForbiddenException('Current version is already in status DRAFT');
        }

        $contentDraft = $this->repository->getContentService()->createContentDraft($contentInfo);

        return new Values\CreatedVersion(
            array(
                'version' => new Values\Version(
                    $contentDraft,
                    $contentType,
                    $this->repository->getContentService()->loadRelations($contentDraft->getVersionInfo())
                ),
            )
        );
    }

    /**
     * A specific draft is updated.
     *
     * @param mixed $contentId
     * @param mixed $versionNumber
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\BadRequestException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\Version
     */
    public function updateVersion($contentId, $versionNumber, Request $request)
    {
        $contentUpdateStruct = $this->inputDispatcher->parse(
            new Message(
                array(
                    'Content-Type' => $request->headers->get('Content-Type'),
                    'Url' => $this->router->generate(
                        'ezpublish_rest_updateVersion',
                        array(
                            'contentId' => $contentId,
                            'versionNumber' => $versionNumber,
                        )
                    ),
                ),
                $request->getContent()
            )
        );

        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo($contentId),
            $versionNumber
        );

        if ($versionInfo->status !== VersionInfo::STATUS_DRAFT) {
            throw new ForbiddenException('Only version in status DRAFT can be updated');
        }

        try {
            $this->repository->getContentService()->updateContent($versionInfo, $contentUpdateStruct);
        } catch (ContentValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (ContentFieldValidationException $e) {
            throw new BadRequestException($e->getMessage());
        }

        $languages = null;
        if ($request->query->has('languages')) {
            $languages = explode(',', $request->query->get('languages'));
        }

        // Reload the content to handle languages GET parameter
        $content = $this->repository->getContentService()->loadContent(
            $contentId,
            $languages,
            $versionInfo->versionNo
        );
        $contentType = $this->repository->getContentTypeService()->loadContentType(
            $content->getVersionInfo()->getContentInfo()->contentTypeId
        );

        return new Values\Version(
            $content,
            $contentType,
            $this->repository->getContentService()->loadRelations($content->getVersionInfo()),
            $request->getPathInfo()
        );
    }

    /**
     * The content version is published.
     *
     * @param mixed $contentId
     * @param mixed $versionNumber
     *
     * @throws ForbiddenException if version $versionNumber isn't a draft
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function publishVersion($contentId, $versionNumber)
    {
        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo($contentId),
            $versionNumber
        );

        if ($versionInfo->status !== VersionInfo::STATUS_DRAFT) {
            throw new ForbiddenException('Only version in status DRAFT can be published');
        }

        $this->repository->getContentService()->publishVersion(
            $versionInfo
        );

        return new Values\NoContent();
    }

    /**
     * Redirects to the relations of the current version.
     *
     * @param mixed $contentId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\TemporaryRedirect
     */
    public function redirectCurrentVersionRelations($contentId)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        return new Values\TemporaryRedirect(
            $this->router->generate(
                'ezpublish_rest_redirectCurrentVersionRelations',
                array(
                    'contentId' => $contentId,
                    'versionNumber' => $contentInfo->currentVersionNo,
                )
            )
        );
    }

    /**
     * Loads the relations of the given version.
     *
     * @param mixed $contentId
     * @param mixed $versionNumber
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RelationList
     */
    public function loadVersionRelations($contentId, $versionNumber, Request $request)
    {
        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : -1;

        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $relationList = $this->repository->getContentService()->loadRelations(
            $this->repository->getContentService()->loadVersionInfo($contentInfo, $versionNumber)
        );

        $relationList = array_slice(
            $relationList,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : null
        );

        $relationListValue = new Values\RelationList(
            $relationList,
            $contentId,
            $versionNumber,
            $request->getPathInfo()
        );

        if ($contentInfo->mainLocationId === null) {
            return $relationListValue;
        }

        return new Values\CachedValue(
            $relationListValue,
            array('locationId' => $contentInfo->mainLocationId)
        );
    }

    /**
     * Loads a relation for the given content object and version.
     *
     * @param mixed $contentId
     * @param int $versionNumber
     * @param mixed $relationId
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestRelation
     */
    public function loadVersionRelation($contentId, $versionNumber, $relationId, Request $request)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $relationList = $this->repository->getContentService()->loadRelations(
            $this->repository->getContentService()->loadVersionInfo($contentInfo, $versionNumber)
        );

        foreach ($relationList as $relation) {
            if ($relation->id == $relationId) {
                $relation = new Values\RestRelation($relation, $contentId, $versionNumber);

                if ($contentInfo->mainLocationId === null) {
                    return $relation;
                }

                return new Values\CachedValue(
                    $relation,
                    array('locationId' => $contentInfo->mainLocationId)
                );
            }
        }

        throw new Exceptions\NotFoundException("Relation not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Deletes a relation of the given draft.
     *
     * @param mixed $contentId
     * @param int   $versionNumber
     * @param mixed $relationId
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function removeRelation($contentId, $versionNumber, $relationId, Request $request)
    {
        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo($contentId),
            $versionNumber
        );

        $versionRelations = $this->repository->getContentService()->loadRelations($versionInfo);
        foreach ($versionRelations as $relation) {
            if ($relation->id == $relationId) {
                if ($relation->type !== Relation::COMMON) {
                    throw new ForbiddenException('Relation is not of type COMMON');
                }

                if ($versionInfo->status !== VersionInfo::STATUS_DRAFT) {
                    throw new ForbiddenException('Relation of type COMMON can only be removed from drafts');
                }

                $this->repository->getContentService()->deleteRelation($versionInfo, $relation->getDestinationContentInfo());

                return new Values\NoContent();
            }
        }

        throw new Exceptions\NotFoundException("Relation not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Creates a new relation of type COMMON for the given draft.
     *
     * @param mixed $contentId
     * @param int $versionNumber
     *
     * @throws ForbiddenException if version $versionNumber isn't a draft
     * @throws ForbiddenException if a relation to the same content already exists
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedRelation
     */
    public function createRelation($contentId, $versionNumber, Request $request)
    {
        $destinationContentId = $this->inputDispatcher->parse(
            new Message(
                array('Content-Type' => $request->headers->get('Content-Type')),
                $request->getContent()
            )
        );

        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $versionInfo = $this->repository->getContentService()->loadVersionInfo($contentInfo, $versionNumber);
        if ($versionInfo->status !== VersionInfo::STATUS_DRAFT) {
            throw new ForbiddenException('Relation of type COMMON can only be added to drafts');
        }

        try {
            $destinationContentInfo = $this->repository->getContentService()->loadContentInfo($destinationContentId);
        } catch (NotFoundException $e) {
            throw new ForbiddenException($e->getMessage());
        }

        $existingRelations = $this->repository->getContentService()->loadRelations($versionInfo);
        foreach ($existingRelations as $existingRelation) {
            if ($existingRelation->getDestinationContentInfo()->id == $destinationContentId) {
                throw new ForbiddenException('Relation of type COMMON to selected destination content ID already exists');
            }
        }

        $relation = $this->repository->getContentService()->addRelation($versionInfo, $destinationContentInfo);

        return new Values\CreatedRelation(
            array(
                'relation' => new Values\RestRelation($relation, $contentId, $versionNumber),
            )
        );
    }

    /**
     * Creates and executes a content view.
     *
     * @deprecated Since platform 1.0. Forwards the request to the new /views location, but returns a 301.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestExecutedView
     */
    public function createView()
    {
        $response = $this->forward('ezpublish_rest.controller.views:createView');

        // Add 301 status code and location href
        $response->setStatusCode(301);
        $response->headers->set('Location', $this->router->generate('ezpublish_rest_views_create'));

        return $response;
    }

    /**
     * @param string $controller
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function forward($controller)
    {
        $path['_controller'] = $controller;
        $subRequest = $this->container->get('request_stack')->getCurrentRequest()->duplicate(null, null, $path);

        return $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
