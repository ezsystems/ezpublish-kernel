<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\TrashItem class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content;

/**
 * this class represents a trash item, which is actually a trashed location.
 */
abstract class TrashItem extends Location
{
    /**
     * Trashed timestamp.
     *
     * @var \DateTime
     */
    protected $trashed;
}
