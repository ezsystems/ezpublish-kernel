<?php

/**
 * File containing the Section Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Section;

/**
 * Section Handler.
 */
abstract class Gateway
{
    /**
     * Inserts a new section with $name and $identifier.
     *
     * @param string $name
     * @param string $identifier
     *
     * @return int The ID of the new section
     */
    abstract public function insertSection($name, $identifier);

    /**
     * Updates section with $id to have $name and $identifier.
     *
     * @param int $id
     * @param string $name
     * @param string $identifier
     */
    abstract public function updateSection($id, $name, $identifier);

    /**
     * Loads data for section with $id.
     *
     * @param int $id
     *
     * @return string[][]
     */
    abstract public function loadSectionData($id);

    /**
     * Loads data for all sections.
     *
     * @return string[][]
     */
    abstract public function loadAllSectionData();

    /**
     * Loads data for section with $identifier.
     *
     * @param string $identifier
     *
     * @return string[][]
     */
    abstract public function loadSectionDataByIdentifier($identifier);

    /**
     * Counts the number of content objects assigned to section with $id.
     *
     * @param int $id
     *
     * @return int
     */
    abstract public function countContentObjectsInSection($id);

    /**
     * Counts the number of role policies using section with $id in their limitations.
     *
     * @param int $id
     *
     * @return int
     */
    abstract public function countPoliciesUsingSection($id);

    /**
     * Counts the number of role assignments using section with $id in their limitations.
     *
     * @param int $id
     *
     * @return int
     */
    abstract public function countRoleAssignmentsUsingSection($id);

    /**
     * Deletes the Section with $id.
     *
     * @param int $id
     */
    abstract public function deleteSection($id);

    /**
     * Inserts the assignment of $contentId to $sectionId.
     *
     * @param int $sectionId
     * @param int $contentId
     */
    abstract public function assignSectionToContent($sectionId, $contentId);
}
