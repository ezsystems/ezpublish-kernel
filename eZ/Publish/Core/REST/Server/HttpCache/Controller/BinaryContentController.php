<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\HttpCache\Controller;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\Core\REST\Server\Values\CachedValue;

class BinaryContentController extends AbstractController
{
    /**
     * @var \eZ\Publish\Core\REST\Server\Controller\BinaryContent
     */
    private $innerController;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;

    public function __construct($innerController, ContentService $contentService)
    {
        $this->innerController = $innerController;
        $this->contentService = $contentService;
    }

    public function getImageVariation($imageId, $variationIdentifier)
    {
        $imageVariation = $this->innerController->getImageVariation($imageId, $variationIdentifier);
        list($contentId) = explode('-', $imageId);

        return new CachedValue(
            $imageVariation,
            $this->getCacheTagsForContentInfo($this->contentService->loadContentInfo($contentId))
        );
    }
}
