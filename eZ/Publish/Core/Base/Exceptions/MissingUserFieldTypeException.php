<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Exceptions;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;

/**
 * @internal
 */
final class MissingUserFieldTypeException extends ContentValidationException
{
    public function __construct(ContentType $contentType, string $fieldType)
    {
        parent::__construct(
            'The provided Content Type "%contentType%" does not contain the %fieldType% Field Type',
            [
                'contentType' => $contentType->identifier,
                'fieldType' => $fieldType,
            ]
        );
    }
}
