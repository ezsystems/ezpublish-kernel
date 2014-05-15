<?php
/**
 * File containing the ValidationError interface
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\FieldType;

use eZ\Publish\API\Repository\Translatable;

/**
 * Interface for validation errors.
 *
 * Enforces to return a translatable message, since it will be necessary to
 * present validation errors to the user. Thus we need plural form handling and
 * replacements of placeholders and so on.
 *
 * @package eZ\Publish\SPI\FieldType
 */
interface ValidationError extends Translatable
{
}

