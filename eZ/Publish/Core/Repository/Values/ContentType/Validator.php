<?php
/**
 * File containing the eZ\Publish\Core\Repository\Values\ContentType\Validator class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\Validator as APIValidator;

/**
 * This class represents a validator provided by a field type.
 * It consists of a name and a set of parameters. This field type implementations
 * are providing a set of concrete validators.
 */
class Validator extends APIValidator
{
}
