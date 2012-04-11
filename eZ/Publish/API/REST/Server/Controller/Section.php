<?php
/**
 * File containing the Section controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Controller;
use eZ\Publish\API\REST\Common\Message;
use eZ\Publish\API\REST\Common\Input;
use eZ\Publish\API\REST\Server\Values;

use \eZ\Publish\API\Repository\SectionService;
use \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;

use Qafoo\RMF;

/**
 * Section controller
 */
class Section
{
    /**
     * Input dispatcher
     *
     * @var \eZ\Publish\API\REST\Server\InputDispatcher
     */
    protected $inputDispatcher;

    /**
     * Section service
     *
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $sectionService;

    /**
     * Construct controller
     *
     * @param Input\Dispatcher $inputDispatcher
     * @param SectionService $sectionService
     * @return void
     */
    public function __construct( Input\Dispatcher $inputDispatcher, SectionService $sectionService )
    {
        $this->inputDispatcher = $inputDispatcher;
        $this->sectionService  = $sectionService;
    }

    /**
     * List sections
     *
     * @param RMF\Request $request
     * @return mixed
     */
    public function listSections( RMF\Request $request )
    {
        $sections = array();

        if ( isset( $request->variables['identifier'] ) )
        {
            $sections = array(
                $this->sectionService->loadSectionByIdentifier(
                    $request->variables['identifier']
                )
            );
        }
        // elseif …
        else
        {
            $sections = $this->sectionService->loadSections();
        }

        return new Values\SectionList( $sections );
    }

    /**
     * Create new section
     *
     * @param RMF\Request $request
     * @return mixed
     */
    public function createSection( RMF\Request $request )
    {
        return new Values\CreatedSection( array(
            'section' => $this->sectionService->createSection(
                $this->inputDispatcher->parse( new Message(
                    array( 'Content-Type' => $request->contentType ),
                    $request->body
                ) )
            ) )
        );
    }

    /**
     * Loads a section
     *
     * @param RMF\Request $request
     * @return Section
     */
    public function loadSection( RMF\Request $request )
    {
        return $this->sectionService->loadSection(
            $request->variables['id']
        );
    }

    /**
     * Updates a section
     *
     * @param RMF\Request $request
     * @return Section
     */
    public function updateSection( RMF\Request $request )
    {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
            )
        );
        return $this->sectionService->updateSection(
            $this->sectionService->loadSection( $request->variables['id'] ),
            $this->mapToUpdateStruct( $createStruct )
        );
    }

    /**
     * Delete a section by ID
     *
     * @param RMF\Request $request
     * @return void
     */
    public function deleteSection( RMF\Request $request )
    {
        return $this->sectionService->deleteSection(
            $this->sectionService->loadSection(
                $request->variables['id']
            )
        );
    }

    /**
     * Maps a SectionCreateStruct to a SectionUpdateStruct.
     *
     * Needed since both structs are encoded into the same media type on input.
     *
     * @param SectionCreateStruct $createStruct
     * @return SectionUpdateStruct
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
