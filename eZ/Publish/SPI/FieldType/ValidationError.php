<?php
/**
 * File containing the ValidationError interface
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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

