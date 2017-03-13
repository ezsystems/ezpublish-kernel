<?php

/**
 * SectionService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\SectionService as SectionServiceInterface;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\SiteAccessAware\Helper\DomainMapper;
use eZ\Publish\Core\Repository\SiteAccessAware\Helper\LanguageResolver;

/**
 * SectionService class.
 */
class SectionService implements SectionServiceInterface
{
    /**
     * Aggregated service.
     *
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $service;

    /**
     * Language resolver
     *
     * @var LanguageResolver
     */
    protected $languageResolver;

    /**
     * Rebuilds existing API domain objects to SiteAccessAware objects
     *
     * @var DomainMapper
     */
    protected $domainMapper;

    /**
     * Constructor.
     *
     * Construct service object from aggregated service
     *
     * @param \eZ\Publish\API\Repository\SectionService $service
     * @param LanguageResolver $languageResolver
     * @param DomainMapper $domainMapper
     */
    public function __construct(
        SectionServiceInterface $service,
        LanguageResolver $languageResolver,
        DomainMapper $domainMapper
    ) {
        $this->service = $service;
        $this->languageResolver = $languageResolver;
        $this->domainMapper = $domainMapper;
    }

    /**
     * Creates the a new Section in the content repository.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create a section
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the new identifier in $sectionCreateStruct already exists
     *
     * @param \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct $sectionCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section The newly create section
     */
    public function createSection(SectionCreateStruct $sectionCreateStruct)
    {
        return $this->service->createSection($sectionCreateStruct);
    }

    /**
     * Updates the given in the content repository.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create a section
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the new identifier already exists (if set in the update struct)
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     * @param \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct $sectionUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function updateSection(Section $section, SectionUpdateStruct $sectionUpdateStruct)
    {
        return $this->service->updateSection($section, $sectionUpdateStruct);
    }

    /**
     * Loads a Section from its id ($sectionId).
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if section could not be found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @param mixed $sectionId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function loadSection($sectionId)
    {
        return $this->service->loadSection($sectionId);
    }

    /**
     * Loads all sections.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @return array of {@link \eZ\Publish\API\Repository\Values\Content\Section}
     */
    public function loadSections()
    {
        return $this->service->loadSections();
    }

    /**
     * Loads a Section from its identifier ($sectionIdentifier).
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if section could not be found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @param string $sectionIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function loadSectionByIdentifier($sectionIdentifier)
    {
        return $this->service->loadSectionByIdentifier($sectionIdentifier);
    }

    /**
     * Counts the contents which $section is assigned to.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     *
     * @return int
     *
     * @deprecated since 6.0
     */
    public function countAssignedContents(Section $section)
    {
        return $this->service->countAssignedContents($section);
    }

    /**
     * Returns true if the given section is assigned to contents, or used in role policies, or in role assignments.
     *
     * This does not check user permissions.
     *
     * @since 6.0
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     *
     * @return bool
     */
    public function isSectionUsed(Section $section)
    {
        return $this->service->isSectionUsed($section);
    }

    /**
     * Assigns the content to the given section
     * this method overrides the current assigned section.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to view provided object
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     */
    public function assignSection(ContentInfo $contentInfo, Section $section)
    {
        return $this->service->assignSection($contentInfo, $section);
    }

    /**
     * Deletes $section from content repository.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified section is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to delete a section
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException  if section can not be deleted
     *         because it is still assigned to some contents.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     */
    public function deleteSection(Section $section)
    {
        return $this->service->deleteSection($section);
    }

    /**
     * Instantiates a new SectionCreateStruct.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct
     */
    public function newSectionCreateStruct()
    {
        return $this->service->newSectionCreateStruct();
    }

    /**
     * Instantiates a new SectionUpdateStruct.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct
     */
    public function newSectionUpdateStruct()
    {
        return $this->service->newSectionUpdateStruct();
    }
}
