<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\FieldType\Relation;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Repository;

class RelationService
{
    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /**
     * RelationService constructor.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Checks if related content is valid.
     *
     * @param int $relatedContentId
     * @return bool
     */
    public function isValid($relatedContentId)
    {
        try {
            /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo */
            $contentInfo = $this->repository->sudo(
                function (Repository $repository) use ($relatedContentId) {
                    return $repository->getContentService()->loadContentInfo($relatedContentId);
                }
            );

            if (!$contentInfo->mainLocationId) {
                // Content was moved to trash!
                return false;
            }

            return true;
        } catch (NotFoundException $e) {
            return false;
        }
    }
}
