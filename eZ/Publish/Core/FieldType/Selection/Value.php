<?php

/**
 * File containing the Selection Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Selection;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for Selection field type.
 */
class Value extends BaseValue
{
    /**
     * Selection content.
     *
     * @var int[]
     */
    public $selection;

    /**
     * Construct a new Value object and initialize it $selection.
     *
     * @param int[] $selection
     */
    public function __construct(array $selection = [])
    {
        $this->selection = $selection;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return implode(',', $this->selection);
    }
}
