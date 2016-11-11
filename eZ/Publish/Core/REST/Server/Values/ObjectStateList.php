<?php

/**
 * File containing the ObjectStateList class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * ObjectState list view model.
 */
class ObjectStateList extends RestValue
{
    /**
     * Object states.
     *
     * @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectState[]
     */
    public $states;

    /**
     * ID of the group that the states belong to.
     *
     * @var mixed
     */
    public $groupId;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState[] $states
     * @param mixed $groupId
     */
    public function __construct(array $states, $groupId)
    {
        $this->states = $states;
        $this->groupId = $groupId;
    }
}
