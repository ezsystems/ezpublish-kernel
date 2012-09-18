<?php
/**
 * File containing the Section controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Input;
use eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;
use eZ\Publish\Core\REST\Server\Values\ResourceDeleted;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException;

use Qafoo\RMF;

/**
 * Section controller
 */
class Section
{
    /**
     * Input dispatcher
     *
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    protected $inputDispatcher;

    /**
     * URL handler
     *
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    protected $urlHandler;

    /**
     * Section service
     *
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $sectionService;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\SectionService $sectionService
     */
    public function __construct( Input\Dispatcher $inputDispatcher, UrlHandler $urlHandler, SectionService $sectionService )
    {
        $this->inputDispatcher = $inputDispatcher;
        $this->urlHandler      = $urlHandler;
        $this->sectionService  = $sectionService;
    }

    /**
     * List sections
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\SectionList
     */
    public function listSections( RMF\Request $request )
    {
        return new Values\SectionList(
            $this->sectionService->loadSections()
        );
    }

    /**
     * Load section by identifier
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\SectionList
     */
    public function loadSectionByIdentifier( RMF\Request $request )
    {
        return new Values\SectionList(
            array(
                $this->sectionService->loadSectionByIdentifier(
                    // GET variable
                    $request->variables['identifier']
                )
            )
        );
    }

    /**
     * Create new section
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedSection
     */
    public function createSection( RMF\Request $request )
    {
        try
        {
            $createdSection = $this->sectionService->createSection(
                $this->inputDispatcher->parse(
                    new Message(
                        array( 'Content-Type' => $request->contentType ),
                        $request->body
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
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function loadSection( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'section', $request->path );
        return $this->sectionService->loadSection( $values['section'] );
    }

    /**
     * Updates a section
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function updateSection( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'section', $request->path );
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
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
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
     */
    public function deleteSection( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'section', $request->path );
        $this->sectionService->deleteSection(
            $this->sectionService->loadSection( $values['section'] )
        );

        return new ResourceDeleted();
    }

    /**
     * Maps a SectionCreateStruct to a SectionUpdateStruct.
     *
     * Needed since both structs are encoded into the same media type on input.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct $createStruct
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
