<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use eZ\Publish\Core\Event\AfterEvent;

final class UpdateFieldDefinitionEvent extends AfterEvent
{
    public const NAME = 'ezplatform.event.content_type.update_field_definition';

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    private $contentTypeDraft;

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    private $fieldDefinition;

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     */
    private $fieldDefinitionUpdateStruct;

    public function __construct(
        ContentTypeDraft $contentTypeDraft,
        FieldDefinition $fieldDefinition,
        FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
    ) {
        $this->contentTypeDraft = $contentTypeDraft;
        $this->fieldDefinition = $fieldDefinition;
        $this->fieldDefinitionUpdateStruct = $fieldDefinitionUpdateStruct;
    }

    public function getContentTypeDraft(): ContentTypeDraft
    {
        return $this->contentTypeDraft;
    }

    public function getFieldDefinition(): FieldDefinition
    {
        return $this->fieldDefinition;
    }

    public function getFieldDefinitionUpdateStruct(): FieldDefinitionUpdateStruct
    {
        return $this->fieldDefinitionUpdateStruct;
    }
}
