<?php
/**
 * File containing the FormAwareFieldTypeInterface interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\FieldType;

/**
 * Interface for field types that can provide form objects (for edition)
 */
interface FormAwareFieldTypeInterface
{
    /**
     * Returns the form type object for the current field type or null if the field type is read only.
     *
     * @return \eZ\Publish\Core\MVC\Symfony\FieldType\FieldTypeForm|null
     */
    public function getFormType();
}
