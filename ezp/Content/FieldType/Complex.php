<?php
/**
 * File containing the Complex FieldType abstract class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType;
use ezp\Content\FieldType;

/**
 * Base class for complex field types.
 * Complex field types are the ones which need further logic than standard field types,
 * by the use of a handler to manipulate data
 */
abstract class Complex extends FieldType
{
    /**
     * Returns a handler, aka. a helper object which aids in the manipulation of
     * complex field type values.
     *
     * @abstract
     * @return null|ezp\Content\FieldType\Handler
     */
    abstract public function getHandler();
}
