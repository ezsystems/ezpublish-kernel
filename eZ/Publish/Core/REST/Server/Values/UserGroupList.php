<?php

/**
 * File containing the UserGroupList class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * User group list view model.
 */
class UserGroupList extends RestValue
{
    /**
     * User groups.
     *
     * @var \eZ\Publish\Core\REST\Server\Values\RestUserGroup[]
     */
    public $userGroups;

    /**
     * Path which was used to fetch the list of user groups.
     *
     * @var string
     */
    public $path;

    /**
     * Construct.
     *
     * @param \eZ\Publish\Core\REST\Server\Values\RestUserGroup[] $userGroups
     * @param string $path
     */
    public function __construct(array $userGroups, $path)
    {
        $this->userGroups = $userGroups;
        $this->path = $path;
    }
}
