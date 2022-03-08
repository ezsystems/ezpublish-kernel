<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Core\FieldType\Validator;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\Core\FieldType\ValidationError;

/**
 * Validator for checking existence of content.
 */
final class TargetContentValidator implements TargetContentValidatorInterface
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    public function __construct(?ContentService $contentService = null)
    {
        $this->contentService = $contentService;
    }

    /**
     * @param mixed $value
     */
    public function validate($value): ?ValidationError
    {
        try {
            $this->contentService->loadContentInfo($value);
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
