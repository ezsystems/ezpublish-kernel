<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used for updating a content type group.
 */
class ContentTypeGroupUpdateStruct extends ValueObject
{
    /**
     * Readable and unique string identifier of a group.
     *
     * @var string
     */
    public $identifier;

    /**
     * If set this value overrides the current user as modifier.
     *
     * @var mixed
     */
    public $modifierId = null;

    /**
     * If set this value overrides the current time for modified.
     *
     * @var \DateTime
     */
    public $modificationDate = null;
}
