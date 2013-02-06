<?php
/**
 * File containing the BinaryFile Value class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\BinaryFile;

use eZ\Publish\Core\FieldType\BinaryBase\Value as BaseValue;

/**
 * Value for BinaryFile field type
 */
class Value extends BaseValue
{
    /**
     * Number of times the file has been downloaded through content/download module
     *
     * @var int
     */
    public $downloadCount = 0;
}
