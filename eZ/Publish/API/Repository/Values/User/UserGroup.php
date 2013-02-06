<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\UserGroup class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
     * @var int
     */
    protected $subGroupCount;
}
