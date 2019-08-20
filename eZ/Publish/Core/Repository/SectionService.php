<?php

/**
 * File containing the eZ\Publish\Core\Repository\SectionService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository;

use Exception;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\API\Repository\PermissionCriterionResolver;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\SectionService as SectionServiceInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd as CriterionLogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot as CriterionLogicalNot;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree as CriterionSubtree;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Persistence\Content\Section as SPISection;
use eZ\Publish\SPI\Persistence\Content\Section\Handler as SectionHandler;
use function array_filter;

/**
 * Section service, used for section operations.
 */
class SectionService implements SectionServiceInterface
{
    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    protected $permissionResolver;

    /** @var \eZ\Publish\API\Repository\PermissionCriterionResolver */
    protected $permissionCriterionResolver;

    /** @var \eZ\Publish\SPI\Persistence\Content\Section\Handler */
    protected $sectionHandler;

    /** @var \eZ\Publish\SPI\Persistence\Content\Location\Handler */
    protected $locationHandler;

    /** @var array */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Content\Section\Handler $sectionHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $locationHandler
     * @param \eZ\Publish\API\Repository\PermissionCriterionResolver $permissionCriterionResolver
     * @param array $settings
     */
    public function __construct(RepositoryInterface $repository, SectionHandler $sectionHandler, LocationHandler $locationHandler, PermissionCriterionResolver $permissionCriterionResolver, array $settings = [])
    {
        $this->repository = $repository;
        $this->sectionHandler = $sectionHandler;
        $this->locationHandler = $locationHandler;
        $this->permissionResolver = $repository->getPermissionResolver();
        $this->permissionCriterionResolver = $permissionCriterionResolver;
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + [
            //'defaultSetting' => array(),
        ];
    }

    /**
     * Creates a new Section in the content repository.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create a section
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the new identifier in $sectionCreateStruct already exists
     *
     * @param \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct $sectionCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section The newly created section
     */
    public function createSection(SectionCreateStruct $sectionCreateStruct)
    {
        if (!is_string($sectionCreateStruct->name) || empty($sectionCreateStruct->name)) {
            throw new InvalidArgumentValue('name', $sectionCreateStruct->name, 'SectionCreateStruct');
        }

        if (!is_string($sectionCreateStruct->identifier) || empty($sectionCreateStruct->identifier)) {
            throw new InvalidArgumentValue('identifier', $sectionCreateStruct->identifier, 'SectionCreateStruct');
        }

        if (!$this->permissionResolver->canUser('section', 'edit', $sectionCreateStruct)) {
            throw new UnauthorizedException('section', 'edit');
        }

        try {
            $existingSection = $this->sectionHandler->loadByIdentifier($sectionCreateStruct->identifier);
            if ($existingSection !== null) {
                throw new InvalidArgumentException('sectionCreateStruct', 'section with specified identifier already exists');
            }
        } catch (APINotFoundException $e) {
            // Do nothing
        }

        $this->repository->beginTransaction();
        try {
            $spiSection = $this->sectionHandler->create(
                $sectionCreateStruct->name,
                $sectionCreateStruct->identifier
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildDomainSectionObject($spiSection);
    }

    /**
     * Updates the given section in the content repository.
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
        if ($sectionUpdateStruct->name !== null && !is_string($sectionUpdateStruct->name)) {
            throw new InvalidArgumentValue('name', $section->name, 'Section');
        }

        if ($sectionUpdateStruct->identifier !== null && !is_string($sectionUpdateStruct->identifier)) {
            throw new InvalidArgumentValue('identifier', $section->identifier, 'Section');
        }

        if (!$this->permissionResolver->canUser('section', 'edit', $section)) {
            throw new UnauthorizedException('section', 'edit');
        }

        if ($sectionUpdateStruct->identifier !== null) {
            try {
                $existingSection = $this->sectionHandler->loadByIdentifier($sectionUpdateStruct->identifier);

                // Allowing identifier update only for the same section
                if ($existingSection->id != $section->id) {
                    throw new InvalidArgumentException('sectionUpdateStruct', 'section with specified identifier already exists');
                }
            } catch (APINotFoundException $e) {
                // Do nothing
            }
        }

        $loadedSection = $this->sectionHandler->load($section->id);

        $this->repository->beginTransaction();
        try {
            $spiSection = $this->sectionHandler->update(
                $loadedSection->id,
                $sectionUpdateStruct->name ?: $loadedSection->name,
                $sectionUpdateStruct->identifier ?: $loadedSection->identifier
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildDomainSectionObject($spiSection);
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
        $section = $this->buildDomainSectionObject(
            $this->sectionHandler->load($sectionId)
        );

        if (!$this->permissionResolver->canUser('section', 'view', $section)) {
            throw new UnauthorizedException('section', 'view');
        }

        return $section;
    }

    /**
     * Loads all sections, excluding the ones the current user is not allowed to read.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section[]
     */
    public function loadSections()
    {
        $sections = array_map(function ($spiSection) {
            return $this->buildDomainSectionObject($spiSection);
        }, $this->sectionHandler->loadAll());

        return array_values(array_filter($sections, function ($section) {
            return $this->permissionResolver->canUser('section', 'view', $section);
        }));
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
        if (!is_string($sectionIdentifier) || empty($sectionIdentifier)) {
            throw new InvalidArgumentValue('sectionIdentifier', $sectionIdentifier);
        }

        $section = $this->buildDomainSectionObject(
            $this->sectionHandler->loadByIdentifier($sectionIdentifier)
        );

        if (!$this->permissionResolver->canUser('section', 'view', $section)) {
            throw new UnauthorizedException('section', 'view');
        }

        return $section;
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
        return $this->sectionHandler->assignmentsCount($section->id);
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
        return $this->sectionHandler->assignmentsCount($section->id) > 0 ||
               $this->sectionHandler->policiesCount($section->id) > 0 ||
               $this->sectionHandler->countRoleAssignmentsUsingSection($section->id) > 0;
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
        $loadedContentInfo = $this->repository->getContentService()->loadContentInfo($contentInfo->id);
        $loadedSection = $this->loadSection($section->id);

        if (!$this->permissionResolver->canUser('section', 'assign', $loadedContentInfo, [$loadedSection])) {
            throw new UnauthorizedException(
                'section',
                'assign',
                [
                    'name' => $loadedSection->name,
                    'content-name' => $loadedContentInfo->name,
                ]
            );
        }

        $this->repository->beginTransaction();
        try {
            $this->sectionHandler->assign(
                $loadedSection->id,
                $loadedContentInfo->id
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Assigns the subtree to the given section
     * this method overrides the current assigned section.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     */
    public function assignSectionToSubtree(Location $location, Section $section): void
    {
        $loadedSubtree = $this->repository->getLocationService()->loadLocation($location->id);
        $loadedSection = $this->loadSection($section->id);

        /**
         * Check read access to whole source subtree.
         *
         * @var bool|\eZ\Publish\API\Repository\Values\Content\Query\Criterion
         */
        $sectionAssignCriterion = $this->permissionCriterionResolver->getPermissionsCriterion(
            'section', 'assign', [$loadedSection]
        );
        if ($sectionAssignCriterion === false) {
            throw new UnauthorizedException('section', 'assign', [
                'name' => $loadedSection->name,
                'subtree' => $loadedSubtree->pathString,
            ]);
        } elseif ($sectionAssignCriterion !== true) {
            // Query if there are any content in subtree current user don't have access to
            $query = new Query(
                [
                    'limit' => 0,
                    'filter' => new CriterionLogicalAnd(
                        [
                            new CriterionSubtree($loadedSubtree->pathString),
                            new CriterionLogicalNot($sectionAssignCriterion),
                        ]
                    ),
                ]
            );

            $result = $this->repository->getSearchService()->findContent($query, [], false);
            if ($result->totalCount > 0) {
                throw new UnauthorizedException('section', 'assign', [
                    'name' => $loadedSection->name,
                    'subtree' => $loadedSubtree->pathString,
                ]);
            }
        }

        $this->repository->beginTransaction();
        try {
            $this->locationHandler->setSectionForSubtree(
                $loadedSubtree->id,
                $loadedSection->id
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Deletes $section from content repository.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified section is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to delete a section
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If section can not be deleted
     *         because it is still assigned to some contents,
     *         or because it is still being used in policy limitations.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     */
    public function deleteSection(Section $section)
    {
        $loadedSection = $this->loadSection($section->id);

        if (!$this->permissionResolver->canUser('section', 'edit', $loadedSection)) {
            throw new UnauthorizedException('section', 'edit', ['sectionId' => $loadedSection->id]);
        }

        if ($this->sectionHandler->assignmentsCount($loadedSection->id) > 0) {
            throw new BadStateException('section', 'section is still assigned to content');
        }

        if ($this->sectionHandler->policiesCount($loadedSection->id) > 0) {
            throw new BadStateException('section', 'section is still being used in policy limitations');
        }

        $this->repository->beginTransaction();
        try {
            $this->sectionHandler->delete($loadedSection->id);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Instantiates a new SectionCreateStruct.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct
     */
    public function newSectionCreateStruct()
    {
        return new SectionCreateStruct();
    }

    /**
     * Instantiates a new SectionUpdateStruct.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct
     */
    public function newSectionUpdateStruct()
    {
        return new SectionUpdateStruct();
    }

    /**
     * Builds API Section object from provided SPI Section object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Section $spiSection
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    protected function buildDomainSectionObject(SPISection $spiSection)
    {
        return new Section(
            [
                'id' => $spiSection->id,
                'identifier' => $spiSection->identifier,
                'name' => $spiSection->name,
            ]
        );
    }
}
