<?php
/**
 * File containing the Section controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
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

/**
 * Section controller
 */
class Section extends RestController
{
    /**
     * Section service
     *
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $sectionService;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\API\Repository\SectionService $sectionService
     */
    public function __construct( SectionService $sectionService )
    {
        $this->sectionService  = $sectionService;
    }

    /**
     * List sections
     *
     * @return \eZ\Publish\Core\REST\Server\Values\SectionList
     */
    public function listSections()
    {
        if ( isset( $this->request->variables['identifier'] ) )
        {
            $sections = array(
                $this->loadSectionByIdentifier()
            );
        }
        else
        {
            $sections = $this->sectionService->loadSections();
        }

        return new Values\SectionList( $sections, $this->request->path );
    }

    /**
     * Loads section by identifier
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function loadSectionByIdentifier()
    {
        return $this->sectionService->loadSectionByIdentifier(
            // GET variable
            $this->request->variables['identifier']
        );
    }

    /**
     * Create new section
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedSection
     */
    public function createSection()
    {
        try
        {
            $createdSection = $this->sectionService->createSection(
                $this->inputDispatcher->parse(
                    new Message(
                        array( 'Content-Type' => $this->request->contentType ),
                        $this->request->body
                    )
                )
            );
        }
        catch ( InvalidArgumentException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }

        return new Values\CreatedSection(
            array(
                'section' => $createdSection
            )
        );
    }

    /**
     * Loads a section
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function loadSection()
    {
        $values = $this->urlHandler->parse( 'section', $this->request->path );
        return $this->sectionService->loadSection( $values['section'] );
    }

    /**
     * Updates a section
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function updateSection()
    {
        $values = $this->urlHandler->parse( 'section', $this->request->path );
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        try
        {
            return $this->sectionService->updateSection(
                $this->sectionService->loadSection( $values['section'] ),
                $this->mapToUpdateStruct( $createStruct )
            );
        }
        catch ( InvalidArgumentException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }
    }

    /**
     * Delete a section by ID
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteSection()
    {
        $values = $this->urlHandler->parse( 'section', $this->request->path );
        $this->sectionService->deleteSection(
            $this->sectionService->loadSection( $values['section'] )
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
    protected function mapToUpdateStruct( SectionCreateStruct $createStruct )
    {
        return new SectionUpdateStruct(
            array(
                'name'       => $createStruct->name,
                'identifier' => $createStruct->identifier,
            )
        );
    }
}
