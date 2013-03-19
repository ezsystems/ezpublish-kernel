<?php
/**
 * File containing the FieldType class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\FieldType;

use eZ\Publish\Core\FieldType\FieldType as BaseFieldType;

/**
 * Base field type class.
 * This subclass adds support for form objects.
 */
abstract class FieldType extends BaseFieldType implements FormAwareFieldTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
    }
}
