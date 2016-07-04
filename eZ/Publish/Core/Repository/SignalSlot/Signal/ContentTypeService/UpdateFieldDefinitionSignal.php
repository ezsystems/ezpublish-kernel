<?php

/**
 * UpdateFieldDefinitionSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService;

use eZ\Publish\Core\Repository\SignalSlot\Signal;

/**
 * UpdateFieldDefinitionSignal class.
 */
class UpdateFieldDefinitionSignal extends Signal
{
    /**
     * ContentTypeDraftId.
     *
     * @var mixed
     */
    public $contentTypeDraftId;

    /**
     * FieldDefinitionId.
     *
     * @var mixed
     */
    public $fieldDefinitionId;
}
