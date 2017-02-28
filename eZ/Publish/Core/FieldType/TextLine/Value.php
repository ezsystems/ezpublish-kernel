<?php

/**
 * File containing the TextLine Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\TextLine;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for TextLine field type.
 */
class Value extends BaseValue
{
    /**
     * Text content.
     *
     * @var string
     */
    public $text;

    /**
     * Construct a new Value object and initialize it $text.
     *
     * @param string $text
     */
    public function __construct($text = '')
    {
        $this->text = $text;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->text;
    }
}
