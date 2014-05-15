<?php
/**
 * File containing the custom field interface
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Query;

/**
 * Interface for criteria, which define a custom field mapping
 *
 * Allows to map the field in a certain type to a custom colum / field / index
 * in the search backend and retrieve it back from the criterion. The SPI
 * implementation may or may not handle this information for criteria
 * implementing this interface.
 */
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
     * Return the custom field to query if set
     *
     * @param string $type
     * @param string $field
     * @return mixed If no custom field is set, return null
     */
    public function getCustomField( $type, $field );
}
