<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Core\FieldType\Validator;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\Core\FieldType\ValidationError;

/**
 * Validator for checking existence of content and its content type.
 */
final class TargetContentValidator implements TargetContentValidatorInterface
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
    }

    public function validate(int $value, array $allowedContentTypes = []): ?ValidationError
    {
        try {
            $contentInfo = $this->contentService->loadContentInfo($value);
            $contentType = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);

            if (!empty($allowedContentTypes) && !in_array($contentType->identifier, $allowedContentTypes, true)) {
                return new ValidationError(
                    'Content Type %contentTypeIdentifier% is not a valid relation target',
                    null,
                    [
                        '%contentTypeIdentifier%' => $contentType->identifier,
                    ],
                    'targetContentId'
                );
            }
        } catch (NotFoundException | UnauthorizedException $e) {
            return new ValidationError(
                'Content with identifier %contentId% is not a valid relation target',
                null,
                [
                    '%contentId%' => $value,
                ],
                'targetContentId'
            );
        }

        return null;
    }
}
