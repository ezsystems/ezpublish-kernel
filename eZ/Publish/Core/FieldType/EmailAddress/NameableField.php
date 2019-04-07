<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\EmailAddress;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Persistence\TransformationProcessor;
use eZ\Publish\SPI\FieldType\Nameable;
use eZ\Publish\SPI\FieldType\Value;

class NameableField implements Nameable
{
    /**
     * String transformation processor, used to normalize sort string as needed.
     *
     * @var \eZ\Publish\Core\Persistence\TransformationProcessor
     */
    private $transformationProcessor;

    public function __construct(TransformationProcessor $transformationProcessor)
    {
        $this->transformationProcessor = $transformationProcessor;
    }

    /**
     * @param \eZ\Publish\Core\FieldType\Relation\Value $value
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param string $languageCode
     *
     * @return string
     */
    public function getFieldName(Value $value, FieldDefinition $fieldDefinition, $languageCode)
    {
        return $this->transformationProcessor->transformByGroup((string)$value, 'lowercase');
    }
}
