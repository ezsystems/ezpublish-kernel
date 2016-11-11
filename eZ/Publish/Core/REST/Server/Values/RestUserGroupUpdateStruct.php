<?php

/**
 * File containing the RestUserGroupUpdateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * RestUserGroupUpdateStruct view model.
 */
class RestUserGroupUpdateStruct extends RestValue
{
    /**
     * UserGroup update struct.
     *
     * @var \eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct
     */
    public $userGroupUpdateStruct;

    /**
     * If set, section of the UserGroup will be updated.
     *
     * @var mixed
     */
    public $sectionId;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct $userGroupUpdateStruct
     * @param mixed $sectionId
     */
    public function __construct(UserGroupUpdateStruct $userGroupUpdateStruct, $sectionId = null)
    {
        $this->userGroupUpdateStruct = $userGroupUpdateStruct;
        $this->sectionId = $sectionId;
    }
}
