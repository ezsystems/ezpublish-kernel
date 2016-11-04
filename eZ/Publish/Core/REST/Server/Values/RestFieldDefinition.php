<?php

/**
 * File containing the RestFieldDefinition class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * RestFieldDefinition view model.
 */
class RestFieldDefinition extends RestValue
{
    /**
     * ContentType the field definitions belong to.
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public $contentType;

    /**
     * Field definition.
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    public $fieldDefinition;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     */
    public function __construct(ContentType $contentType, FieldDefinition $fieldDefinition)
    {
        $this->contentType = $contentType;
        $this->fieldDefinition = $fieldDefinition;
    }
}
