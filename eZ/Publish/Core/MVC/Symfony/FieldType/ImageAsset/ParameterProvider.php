<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\FieldType\ImageAsset;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute;

class ParameterProvider implements ParameterProviderInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        Repository $repository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->repository = $repository;
        $this->authorizationChecker = $authorizationChecker;
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
        } catch (NotFoundException | UnauthorizedException $exception) {
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
        $readAttribute = new Attribute('content', 'read', [
            'valueObject' => $contentInfo,
        ]);

        $viewEmbedAttribute = new Attribute('content', 'view_embed', [
            'valueObject' => $contentInfo,
        ]);

        return $this->authorizationChecker->isGranted($readAttribute) || $this->authorizationChecker->isGranted($viewEmbedAttribute);
    }
}
