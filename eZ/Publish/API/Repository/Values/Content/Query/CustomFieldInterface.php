<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query;

/**
 * Interface for criteria and sort clauses, which defines a custom field mapping.
 *
 * Allows to map the field in a certain type to a custom column / field / index
 * in the search backend and retrieve it back from the criterion. The SPI
 * implementation may or may not handle this information for criteria and
 * sort clauses implementing this interface.
 */
interface CustomFieldInterface
{
    /**
     * Set a custom field to query or sort on.
     *
     * Set a custom field to query or sort on for a defined field in a defined type.
     *
     * @param string $type
     * @param string $field
     * @param string $customField
     */
    public function setCustomField(string $type, string $field, string $customField): void;

    /**
     * Return the custom field to query or sort on if set.
     *
     * @param string $type
     * @param string $field
     *
     * @return string|null If no custom field is set, return null
     */
    public function getCustomField(string $type, string $field): ?string;
}
