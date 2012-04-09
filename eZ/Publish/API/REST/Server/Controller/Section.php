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
        return new Values\SectionList(
            $this->sectionService->loadSections()
        );
    }

    /**
     * Create new section
     *
     * @param RMF\Request $request
     * @return mixed
     */
    public function createSection( RMF\Request $request )
    {
        return $this->sectionService->createSection(
            $this->inputDispatcher->parse( new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
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
            $this->loadSection( $request->variables['id'] ),
            $this->mapToUpdateStruct( $createStruct )
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
