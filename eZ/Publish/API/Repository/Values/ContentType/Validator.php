<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\ContentType\Validator class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a validatoor provided by a field type.
 * It consists of a name and a set of paraameters. This field type implementations
 * are providing a set of concrete validators.
 */
abstract class Validator extends ValueObject
{
    /**
     * The name of the validator
     * @var string
     */
    public $name;

    /**
     * a map of the parameters of the validator
     *
     * @var array
     */
    public $parameters;
}
