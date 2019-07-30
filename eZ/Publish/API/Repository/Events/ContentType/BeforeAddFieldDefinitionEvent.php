<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;

final class BeforeAddFieldDefinitionEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft */
    private $contentTypeDraft;

    /** @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct */
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
