<?php

/**
 * File containing the RelationList FieldType Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RelationList;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for RelationList field type.
 */
class Value extends BaseValue
{
    /**
     * Related content id's.
     *
     * @var mixed[]
     */
    public $destinationContentIds;

    /**
     * Construct a new Value object and initialize it $text.
     *
     * @param mixed[] $destinationContentIds
     */
    public function __construct(array $destinationContentIds = [])
    {
        $this->destinationContentIds = $destinationContentIds;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return implode(',', $this->destinationContentIds);
    }
}
