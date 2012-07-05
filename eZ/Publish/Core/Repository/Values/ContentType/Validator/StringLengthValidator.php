<?php
/**
 * File containing the eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\StringLengthValidator class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\ContentType\Validator;

use eZ\Publish\API\Repository\Values\ContentType\Validator;

/**
 * This class represents a validator provided by a field type.
 * It consists of a name and a set of parameters.
 * The field type implementations are providing a set of concrete validators.
 *
 * @property-read string $identifier The unique identifier of the validator
 * @property int $maxStringLength The maximum allowed length of the string.
 */
class StringLengthValidator extends Validator
{
    /**
     * The unique identifier of the validator
     *
     * @var string
     */
    protected $identifier = "StringLengthValidator";
}
