<?php

/**
 * File containing the Relation FieldType Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Relation;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for Relation field type.
 */
class Value extends BaseValue
{
    /**
     * Related content.
     *
     * @var mixed|null
     */
    public $destinationContentId;

    /**
     * Construct a new Value object and initialize it $destinationContent.
     *
     * @param mixed $destinationContentId Content id the relation is to
     */
    public function __construct($destinationContentId = null)
    {
        $this->destinationContentId = $destinationContentId;
    }

    /**
     * Returns the related content's name.
     *
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->destinationContentId;
    }
}
