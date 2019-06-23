<?php

/**
 * File containing the Section controller class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Server\Controller as RestController;
use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;
use eZ\Publish\Core\REST\Server\Values\NoContent;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Section controller.
 */
class Section extends RestController
{
    /**
     * Section service.
     *
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $sectionService;

    /**
     * Construct controller.
     *
     * @param \eZ\Publish\API\Repository\SectionService $sectionService
     */
    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }

    /**
     * List sections.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\SectionList
     */
    public function listSections(Request $request)
    {
        if ($request->query->has('identifier')) {
            $sections = [
                $this->loadSectionByIdentifier($request),
            ];
        } else {
            $sections = $this->sectionService->loadSections();
        }

        return new Values\SectionList($sections, $request->getPathInfo());
    }

    /**
     * Loads section by identifier.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function loadSectionByIdentifier(Request $request)
    {
        return $this->sectionService->loadSectionByIdentifier(
            // GET variable
            $request->query->get('identifier')
        );
    }

    /**
     * Create new section.
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedSection
     */
    public function createSection(Request $request)
    {
        try {
            $createdSection = $this->sectionService->createSection(
                $this->inputDispatcher->parse(
                    new Message(
                        ['Content-Type' => $request->headers->get('Content-Type')],
                        $request->getContent()
                    )
                )
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException($e->getMessage());
        }

        return new Values\CreatedSection(
            [
                'section' => $createdSection,
            ]
        );
    }

    /**
     * Loads a section.
     *
     * @param $sectionId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function loadSection($sectionId)
    {
        return $this->sectionService->loadSection($sectionId);
    }

    /**
     * Updates a section.
     *
     * @param $sectionId
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function updateSection($sectionId, Request $request)
    {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            return $this->sectionService->updateSection(
                $this->sectionService->loadSection($sectionId),
                $this->mapToUpdateStruct($createStruct)
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException($e->getMessage());
        }
    }

    /**
     * Delete a section by ID.
     *
     * @param $sectionId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteSection($sectionId)
    {
        $this->sectionService->deleteSection(
            $this->sectionService->loadSection($sectionId)
        );

        return new NoContent();
    }

    /**
     * Maps a SectionCreateStruct to a SectionUpdateStruct.
     *
     * Needed since both structs are encoded into the same media type on input.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct $createStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct
     */
    protected function mapToUpdateStruct(SectionCreateStruct $createStruct)
    {
        return new SectionUpdateStruct(
            [
                'name' => $createStruct->name,
                'identifier' => $createStruct->identifier,
            ]
        );
    }
}
