<?php

/**
 * File containing the Url Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Url;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for Url field type.
 */
class Value extends BaseValue
{
    /**
     * Link content.
     *
     * @var string
     */
    public $link;

    /**
     * Text content.
     *
     * @var string
     */
    public $text;

    /**
     * Construct a new Value object and initialize it with its $link and optional $text.
     *
     * @param string $link
     * @param string $text
     */
    public function __construct($link = null, $text = null)
    {
        $this->link = $link;
        $this->text = $text;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->link;
    }
}
