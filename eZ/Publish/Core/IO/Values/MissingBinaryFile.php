<?php

/**
 * File containing the eZ\Publish\Core\IO\Values\BinaryFile class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\Values;

/**
 * Override of BinaryFile that indicates a non existing file.
 *
 * Used for tolerance of var dir that does not match the database's content.
 */
class MissingBinaryFile extends BinaryFile
{
}
