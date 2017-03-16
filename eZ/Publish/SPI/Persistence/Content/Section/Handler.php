<?php

/**
 * File containing the Section Handler interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content\Section;

interface Handler
{
    /**
     * Create a new section.
     *
     * @param string $name
     * @param string $identifier
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     *
     * @todo Should validate that $identifier is unique??
     * @todo What about translatable $name?
     */
    public function create($name, $identifier);

    /**
     * Update name and identifier of a section.
     *
     * @param mixed $id
     * @param string $name
     * @param string $identifier
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     */
    public function update($id, $name, $identifier);

    /**
     * Get section data.
     *
     * @param mixed $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If section is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     */
    public function load($id);

    /**
     * Get all section data.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section[]
     */
    public function loadAll();

    /**
     * Get section data by identifier.
     *
     * @param string $identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If section is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     */
    public function loadByIdentifier($identifier);

    /**
     * Delete a section.
     *
     * Might throw an exception if the section is still associated with some
     * content objects. Make sure that no content objects are associated with
     * the section any more *before* calling this method.
     *
     * @param mixed $id
     */
    public function delete($id);

    /**
     * Assigns section to single content object.
     *
     * @param mixed $sectionId
     * @param mixed $contentId
     */
    public function assign($sectionId, $contentId);

    /**
     * Number of content assignments a Section has.
     *
     * @param mixed $sectionId
     *
     * @return int
     */
    public function assignmentsCount($sectionId);

    /**
     * Number of role policies using a Section in limitations.
     *
     * @param mixed $sectionId
     *
     * @return int
     */
    public function policiesCount($sectionId);

    /**
     * Counts the number of role assignments using section with $sectionId in their limitations.
     *
     * @param int $sectionId
     *
     * @return int
     */
    public function countRoleAssignmentsUsingSection($sectionId);
}
