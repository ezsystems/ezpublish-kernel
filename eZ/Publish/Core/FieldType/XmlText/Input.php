<?php

/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Input class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\XmlText;

abstract class Input
{
    /**
     * Contains the internal representation of the XmlText field type.
     *
     * @var string
     */
    protected $internalRepresentation;

    /**
     * Returns the internal representation of the XmlText field type.
     *
     * @return string
     */
    final public function getInternalRepresentation()
    {
        return $this->internalRepresentation;
    }
}
