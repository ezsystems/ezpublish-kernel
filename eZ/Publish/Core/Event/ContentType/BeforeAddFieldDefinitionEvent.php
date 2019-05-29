<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeAddFieldDefinitionEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.content_type.add_field_definition.before';

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    private $contentTypeDraft;

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct
     */
    private $fieldDefinitionCreateStruct;

    public function __construct(ContentTypeDraft $contentTypeDraft, FieldDefinitionCreateStruct $fieldDefinitionCreateStruct)
    {
        $this->contentTypeDraft = $contentTypeDraft;
        $this->fieldDefinitionCreateStruct = $fieldDefinitionCreateStruct;
    }

    public function getContentTypeDraft(): ContentTypeDraft
    {
        return $this->contentTypeDraft;
    }

    public function getFieldDefinitionCreateStruct(): FieldDefinitionCreateStruct
    {
        return $this->fieldDefinitionCreateStruct;
    }
}
