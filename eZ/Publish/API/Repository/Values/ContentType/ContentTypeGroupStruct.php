<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ValueObject;

abstract class ContentTypeGroupStruct extends ValueObject
{
    /**
     * Readable and unique string identifier of a group.
     *
     * @var string
     */
    public $identifier;
}
