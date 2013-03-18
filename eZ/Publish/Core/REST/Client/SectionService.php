<?php
/**
 * File containing the SectionService class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client;

use eZ\Publish\API\Repository\SectionService as APISectionService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;

use eZ\Publish\Core\REST\Common\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\REST\Common\Exceptions\ForbiddenException;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Input\Dispatcher;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Values\RestContentMetadataUpdateStruct;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\SectionService}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\SectionService
 */
class SectionService implements APISectionService, Sessionable
{
    /**
     * @var \eZ\Publish\Core\REST\Client\HttpClient
     */
    private $client;

    /**
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    private $inputDispatcher;

    /**
     * @var \eZ\Publish\Core\REST\Common\Output\Visitor
     */
    private $outputVisitor;

    /**
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    private $urlHandler;

    /**
     * @param \eZ\Publish\Core\REST\Client\HttpClient $client
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $outputVisitor
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     */
    public function __construct( HttpClient $client, Dispatcher $inputDispatcher, Visitor $outputVisitor, UrlHandler $urlHandler )
    {
        $this->client          = $client;
        $this->inputDispatcher = $inputDispatcher;
        $this->outputVisitor   = $outputVisitor;
        $this->urlHandler      = $urlHandler;
    }

    /**
     * Set session ID
     *
     * Only for testing
     *
     * @param mixed $id
     *
     * @private
     *
     * @return void
     */
    public function setSession( $id )
    {
        if ( $this->client instanceof Sessionable )
        {
            $this->client->setSession( $id );
        }
    }

    /**
     * Creates the a new Section in the content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create a section
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the new identifier in $sectionCreateStruct already exists
     *
     * @param \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct $sectionCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section The newly create section
     */
    public function createSection( SectionCreateStruct $sectionCreateStruct )
    {
        $inputMessage = $this->outputVisitor->visit( $sectionCreateStruct );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'Section' );

        $result = $this->client->request(
            'POST',
            $this->urlHandler->generate( 'sections' ),
            $inputMessage
        );

        try
        {
            return $this->inputDispatcher->parse( $result );
        }
        catch ( ForbiddenException $e )
        {
            throw new InvalidArgumentException( $e->getMessage(), $e->getCode() );
        }
    }

    /**
     * Updates the given in the content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create a section
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the new identifier already exists (if set in the update struct)
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     * @param \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct $sectionUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function updateSection( Section $section, SectionUpdateStruct $sectionUpdateStruct )
    {
        $inputMessage = $this->outputVisitor->visit( $sectionUpdateStruct );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'Section' );
        $inputMessage->headers['X-HTTP-Method-Override'] = 'PATCH';

        // Should originally be PATCH, but PHP's shiny new internal web server
        // dies with it.
        $result = $this->client->request(
            'POST',
            $section->id,
            $inputMessage
        );

        try
        {
            return $this->inputDispatcher->parse( $result );
        }
        catch ( ForbiddenException $e )
        {
            throw new InvalidArgumentException( $e->getMessage(), $e->getCode() );
        }
    }

    /**
     * Loads a Section from its id ($sectionId)
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if section could not be found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @param mixed $sectionId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function loadSection( $sectionId )
    {
        $response = $this->client->request(
            'GET',
            $sectionId,
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'Section' ) )
            )
        );
        return $this->inputDispatcher->parse( $response );
    }

    /**
     * Loads all sections
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @return array of {@link \eZ\Publish\API\Repository\Values\Content\Section}
     */
    public function loadSections()
    {
        $response = $this->client->request(
            'GET',
            $this->urlHandler->generate( 'sections' ),
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'SectionList' ) )
            )
        );
        return $this->inputDispatcher->parse( $response );
    }

    /**
     * Loads a Section from its identifier ($sectionIdentifier)
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if section could not be found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @param string $sectionIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function loadSectionByIdentifier( $sectionIdentifier )
    {
        $response = $this->client->request(
            'GET',
            $this->urlHandler->generate( 'sectionByIdentifier', array( 'section' => $sectionIdentifier ) ),
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'SectionList' ) )
            )
        );
        $result = $this->inputDispatcher->parse( $response );

        return reset( $result );
    }

    /**
     * Counts the contents which $section is assigned to
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     *
     * @return int
     */
    public function countAssignedContents( Section $section )
    {
        throw new \Exception( "@todo: Implement." );
    }

    /**
     * Assigns the content to the given section
     * this method overrides the current assigned section
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to view provided object
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     *
     * @todo In order to make the integration test for this method running, the
     *       countAssignedContents() method must be implemented. Otherwise this
     *       should work fine.
     */
    public function assignSection( ContentInfo $contentInfo, Section $section )
    {
        $inputMessage = $this->outputVisitor->visit(
            new RestContentMetadataUpdateStruct(
                array( 'sectionId' => $section->id )
            )
        );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'Content' );
        $inputMessage->headers['X-HTTP-Method-Override'] = 'PATCH';

        $response = $this->client->request(
            'POST',
            $contentInfo->id,
            $inputMessage
        );

        // Will throw exception on error, no return value for method
        // @todo: Deactivated due to missing implementation of visitor for
        // content on the server side.
        // $result = $this->inputDispatcher->parse( $response );
    }

    /**
     * Deletes $section from content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified section is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to delete a section
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException  if section can not be deleted
     *         because it is still assigned to some contents.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     */
    public function deleteSection( Section $section )
    {
        $response = $this->client->request(
            'DELETE',
            $section->id,
            new Message(
                // @todo: What media-type should we set here? Actually, it should be
                // all expected exceptions + none? Or is "Section" correct,
                // since this is what is to be expected by the resource
                // identified by the URL?
                array( 'Accept' => $this->outputVisitor->getMediaType( 'Section' ) )
            )
        );

        if ( !empty( $response->body ) )
            $this->inputDispatcher->parse( $response );
    }

    /**
     * Instantiates a new SectionCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct
     */
    public function newSectionCreateStruct()
    {
        return new SectionCreateStruct();
    }

    /**
     * Instantiates a new SectionUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct
     */
    public function newSectionUpdateStruct()
    {
        return new SectionUpdateStruct();
    }
}
