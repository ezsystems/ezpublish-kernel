<?php
/**
 * File containing the custom field interface
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Query;

interface CustomFieldInterface
{
    /**
     * Set a custom field to query
     *
     * Set a custom field to query for a defined field in a defined type.
     *
     * @param string $type
     * @param string $field
     * @param string $customField
     * @return void
     */
    public function setCustomField( $type, $field, $customField );

    /**
     * Retun custom field
     *
     * If no custom field is set, return null
     *
     * @param string $type
     * @param string $field
     * @return mixed
     */
    public function getCustomField( $type, $field );
}
