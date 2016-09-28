<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\HttpCache\Controller;

use eZ\Publish\Core\REST\Server\Values\CachedValue;
use Symfony\Component\HttpFoundation\Request;

class ContentController extends AbstractController
{
    /**
     * @var \eZ\Publish\Core\REST\Server\Controller\Content
     */
    private $innerController;

    /**
     * @param \eZ\Publish\Core\REST\Server\Controller\Content|\eZ\Publish\Core\REST\Server\HttpCache\Controller\CachedValueUnwrapperController $contentController
     */
    public function __construct($contentController)
    {
        $this->innerController = $contentController;
    }

    public function redirectContent(Request $request)
    {
        return $this->innerController->redirectContent($request);
    }

    public function loadContent($contentId, Request $request)
    {
        $value = $this->innerController->loadContent($contentId, $request);

        return new CachedValue(
            $value,
            $this->getCacheTagsForContentInfo($value->contentInfo)
        );
    }

    public function updateContentMetadata($contentId, Request $request)
    {
        return $this->innerController->updateContentMetadata($contentId, $request);
    }

    public function redirectCurrentVersion($contentId)
    {
        return new CachedValue(
            $this->innerController->redirectCurrentVersion($contentId),
            ['content' => $contentId]
        );
    }

    public function loadContentInVersion($contentId, $versionNumber, Request $request)
    {
        $value = $this->innerController->loadContentInVersion($contentId, $versionNumber, $request);

        return new CachedValue(
            $value,
            $this->getCacheTagsForContentInfo($value->content->contentInfo)
        );
    }

    public function createContent(Request $request)
    {
        return $this->innerController->createContent($request);
    }

    public function deleteContent($contentId)
    {
        return $this->innerController->deleteContent($contentId);
    }

    public function copyContent($contentId, Request $request)
    {
        return $this->innerController->copyContent($contentId, $request);
    }

    public function loadContentVersions($contentId, Request $request)
    {
        return new CachedValue(
            $this->innerController->loadContentVersions($contentId, $request),
            ['content' => $contentId]
        );
    }

    public function deleteContentVersion($contentId, $versionNumber)
    {
        return $this->innerController->deleteContentVersion($contentId, $versionNumber);
    }

    public function createDraftFromVersion($contentId, $versionNumber)
    {
        return $this->innerController->createDraftFromVersion($contentId, $versionNumber);
    }

    public function createDraftFromCurrentVersion($contentId)
    {
        return $this->innerController->createDraftFromCurrentVersion($contentId);
    }

    public function updateVersion($contentId, $versionNumber, Request $request)
    {
        return $this->innerController->updateVersion($contentId, $versionNumber, $request);
    }

    public function publishVersion($contentId, $versionNumber)
    {
        return $this->innerController->publishVersion($contentId, $versionNumber);
    }

    public function redirectCurrentVersionRelations($contentId)
    {
        return new CachedValue(
            $this->innerController->redirectCurrentVersionRelations($contentId),
            ['content' => $contentId]
        );
    }

    public function loadVersionRelations($contentId, $versionNumber, Request $request)
    {
        return new CachedValue(
            $this->innerController->loadVersionRelations($contentId, $versionNumber, $request),
            ['content' => $contentId]
        );
    }

    public function loadVersionRelation($contentId, $versionNumber, $relationId, Request $request)
    {
        return new CachedValue(
            $this->innerController->loadVersionRelation($contentId, $versionNumber, $relationId, $request),
            ['content' => $contentId]
        );
    }

    public function removeRelation($contentId, $versionNumber, $relationId, Request $request)
    {
        return $this->innerController->removeRelation($contentId, $versionNumber, $relationId, $request);
    }

    public function createRelation($contentId, $versionNumber, Request $request)
    {
        return $this->innerController->createRelation($contentId, $versionNumber, $request);
    }

    public function createView()
    {
        return $this->innerController->createView();
    }
}
