<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\FieldType\ImageAsset;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class ParameterProvider implements ParameterProviderInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \eZ\Publish\API\Repository\PermissionResolver
     */
    private $permissionsResolver;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->permissionsResolver = $repository->getPermissionResolver();
    }

    /**
     * {@inheritdoc}
     */
    public function getViewParameters(Field $field): array
    {
        try {
            $contentInfo = $this->loadContentInfo(
                $field->value->destinationContentId
            );

            if (!$this->userHasPermissions($contentInfo)) {
                return [
                    'available' => false,
                ];
            }

            return ['available' => !$contentInfo->isTrashed()];
        } catch (NotFoundException $exception) {
            return [
                'available' => false,
            ];
        }
    }

    /**
     * @param int $id
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     * @throws \Exception
     */
    private function loadContentInfo(int $id): ContentInfo
    {
        return  $this->repository->sudo(
            function (Repository $repository) use ($id) {
                return $repository->getContentService()->loadContentInfo($id);
            }
        );
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @return bool
     */
    private function userHasPermissions(ContentInfo $contentInfo): bool
    {
        if ($this->permissionsResolver->canUser('content', 'read', $contentInfo)) {
            return true;
        }

        if ($this->permissionsResolver->canUser('content', 'view_embed', $contentInfo)) {
            return true;
        }

        return false;
    }
}
