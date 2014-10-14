<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\UserGroup class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\Content\Content;

/**
 * This class represents a user group
 *
 * @property-read mixed $parentId
 * @property-read int $subGroupCount
 */
abstract class UserGroup extends Content
{
    /**
     * the parent id of the user group
     * @var mixed
     */
    protected $parentId;

    /**
     * The number of sub groups
     * @deprecated As of eZ Publish 5.3.3, count can be obtained on demand using location service.
     * @var int
     */
    protected $subGroupCount;
}
