<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Content\Section;

/**
 * Base class for Section gateways.
 *
 * @internal For internal use by Persistence Handlers.
 */
abstract class Gateway
{
    public const CONTENT_SECTION_SEQ = 'ezsection_id_seq';
    public const CONTENT_SECTION_TABLE = 'ezsection';

    /**
     * Inserts a new section with $name and $identifier.
     *
     * @return int The ID of the new section
     */
    abstract public function insertSection(string $name, string $identifier): int;

    /**
     * Updates section with $id to have $name and $identifier.
     */
    abstract public function updateSection(int $id, string $name, string $identifier): void;

    /**
     * Loads data for section with $id.
     */
    abstract public function loadSectionData(int $id): array;

    /**
     * Loads data for all sections.
     */
    abstract public function loadAllSectionData(): array;

    /**
     * Loads data for section with $identifier.
     */
    abstract public function loadSectionDataByIdentifier(string $identifier): array;

    /**
     * Counts the number of content objects assigned to section with $id.
     */
    abstract public function countContentObjectsInSection(int $id): int;

    /**
     * Counts the number of role policies using section with $id in their limitations.
     */
    abstract public function countPoliciesUsingSection(int $id): int;

    /**
     * Counts the number of role assignments using section with $id in their limitations.
     */
    abstract public function countRoleAssignmentsUsingSection(int $id): int;

    /**
     * Deletes the Section with $id.
     */
    abstract public function deleteSection(int $id): void;

    /**
     * Inserts the assignment of $contentId to $sectionId.
     */
    abstract public function assignSectionToContent(int $sectionId, int $contentId): void;
}
