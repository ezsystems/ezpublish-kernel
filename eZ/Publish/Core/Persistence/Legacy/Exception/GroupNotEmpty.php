<?php

/**
 * File containing the GroupNotEmpty exception class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Exception;

use eZ\Publish\Core\Base\Exceptions\BadStateException;

/**
 * Exception thrown if a Content\Type\Group is to be deleted which is not
 * empty.
 */
class GroupNotEmpty extends BadStateException
{
    /**
     * Creates a new exception for $groupId.
     *
     * @param mixed $groupId
     */
    public function __construct($groupId)
    {
        parent::__construct(
            '$groupId',
            sprintf('Group with ID "%s" is not empty.', $groupId)
        );
    }
}
