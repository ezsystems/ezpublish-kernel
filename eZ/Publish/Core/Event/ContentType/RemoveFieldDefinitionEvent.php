<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Events\ContentType\RemoveFieldDefinitionEvent as RemoveFieldDefinitionEventInteraface;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use Symfony\Contracts\EventDispatcher\Event;

final class RemoveFieldDefinitionEvent extends Event implements RemoveFieldDefinitionEventInteraface
{
    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft */
    private $contentTypeDraft;

    /** @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition */
    private $fieldDefinition;

    public function __construct(
        ContentTypeDraft $contentTypeDraft,
        FieldDefinition $fieldDefinition
    ) {
        $this->contentTypeDraft = $contentTypeDraft;
        $this->fieldDefinition = $fieldDefinition;
    }

    public function getContentTypeDraft(): ContentTypeDraft
    {
        return $this->contentTypeDraft;
    }

    public function getFieldDefinition(): FieldDefinition
    {
        return $this->fieldDefinition;
    }
}
