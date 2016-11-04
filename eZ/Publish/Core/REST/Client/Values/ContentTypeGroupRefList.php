<?php

/**
 * File containing the ContentTypeGroupRefList class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Values;

use eZ\Publish\Core\REST\Client\ContentTypeService;

/**
 * ContentTypeGroupRefList.
 */
class ContentTypeGroupRefList
{
    /**
     * Contains ContentTypeGroupRefList reference.
     *
     * @var string
     */
    public $listReference;

    /**
     * Contains ContentTypeGroup references.
     *
     * @var string[]
     */
    protected $contentTypeGroupReferences;

    /**
     * Content type service.
     *
     * @var ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @param \eZ\Publish\Core\REST\Client\ContentTypeService $contentTypeService
     * @param string $listReference
     * @param string[] $contentTypeGroupReferences
     */
    public function __construct(ContentTypeService $contentTypeService, $listReference, array $contentTypeGroupReferences)
    {
        $this->contentTypeService = $contentTypeService;
        $this->listReference = $listReference;
        $this->contentTypeGroupReferences = $contentTypeGroupReferences;
    }

    /**
     * Fetches and returns the ContentTypeGroups contained in the list.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    public function getContentTypeGroups()
    {
        $contentTypeGroups = array();
        foreach ($this->contentTypeGroupReferences as $reference) {
            $contentTypeGroups[] = $this->contentTypeService->loadContentTypeGroup($reference);
        }

        return $contentTypeGroups;
    }
}
