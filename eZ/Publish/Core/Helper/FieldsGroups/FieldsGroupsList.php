<?php
/**
 * This file is part of the ezpublish-kernel package.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Helper\FieldsGroups;

/**
 * List of content fields groups.
 *
 * Used to group fields definitions, and apply this grouping when editing / viewing content.
 */
interface FieldsGroupsList
{
    /**
     * Returns the list of fields groups.
     * The list is a hash, with the group identifier as the key, and the human readable string as the value.
     * If groups are meant to be translated, they should be translated inside this service.
     *
     * @return array hash, with the group identifier as the key, and the human readable string as the value.
     */
    public function getGroups();

    /**
     * Returns the default field group identifier.
     *
     * @return string
     */
    public function getDefaultGroup();
}
