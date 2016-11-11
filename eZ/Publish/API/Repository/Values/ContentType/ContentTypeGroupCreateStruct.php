<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\ContentType;

/**
 * This class is used for creating a content type group.
 */
class ContentTypeGroupCreateStruct extends ContentTypeGroupStruct
{
    /**
     * If set this value overrides the current user as creator.
     *
     * @var mixed
     */
    public $creatorId = null;

    /**
     * If set this value overrides the current time for creation.
     *
     * @var \DateTime
     */
    public $creationDate = null;
}
