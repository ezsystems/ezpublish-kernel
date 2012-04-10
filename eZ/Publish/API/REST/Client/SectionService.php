<?php
/**
 * File containing the SectionService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Client;

use \eZ\Publish\API\Repository\Values\Content\ContentInfo;
use \eZ\Publish\API\Repository\Values\Content\Section;
use \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;

use \eZ\Publish\API\REST\Common\Input;
use \eZ\Publish\API\REST\Common\Output;
use \eZ\Publish\API\REST\Common\Message;

use \eZ\Publish\API\REST\Client\Sessionable;


/**
 * Implementation of the {@link \eZ\Publish\API\Repository\SectionService}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\SectionService
 */
class SectionService implements \eZ\Publish\API\Repository\SectionService, Sessionable
{
    /**
     * @var \eZ\Publish\API\REST\Client\HttpClient
     */
    private $client;

    /**
     * @var \eZ\Publish\API\REST\Common\Input\Dispatcher
     */
    private $inputDispatcher;

    /**
     * @var \eZ\Publish\API\REST\Common\Output\Visitor
     */
    private $outputVisitor;

    /**
     * @param \eZ\Publish\API\REST\Client\HttpClient $client
     * @param \eZ\Publish\API\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\API\REST\Common\Output\Visitor $outputVisitor
     */
    public function __construct( HttpClient $client, Input\Dispatcher $inputDispatcher, Output\Visitor $outputVisitor )
    {
        $this->client          = $client;
        $this->inputDispatcher = $inputDispatcher;
        $this->outputVisitor   = $outputVisitor;
    }

    /**
     * Set session ID
     *
     * Only for testing
     *
     * @param mixed tringid
     * @return void
     * @private
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
            '/content/sections',
            $inputMessage
        );

        return $this->inputDispatcher->parse( $result );
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
        // dies with it. m(
        $result = $this->client->request(
            'POST',
            sprintf( '/content/sections/%s', $section->id ),
            $inputMessage
        );

        return $this->inputDispatcher->parse( $result );
    }

    /**
     * Loads a Section from its id ($sectionId)
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if section could not be found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @param int $sectionId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function loadSection( $sectionId )
    {
        $response = $this->client->request(
            'GET',
            sprintf( '/content/sections/%s', $sectionId ),
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
            'GET', '/content/sections', new Message(
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
            sprintf( '/content/sections?identifier=%s', $sectionIdentifier ),
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
        throw new \Exception( "@TODO: Implement." );
    }

    /**
     * assigns the content to the given section
     * this method overrides the current assigned section
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to view provided object
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     */
    public function assignSection( ContentInfo $contentInfo, Section $section )
    {
        throw new \Exception( "@TODO: Implement." );
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
            sprintf( '/content/sections/%s', $section->id ),
            new Message(
                // TODO: What media-type should we set here? Actually, it should be
                // all expected exceptions + none? Or is "Section" correct,
                // since this is what is to be expected by the resource
                // identified by the URL?
                array( 'Accept' => $this->outputVisitor->getMediaType( 'Section' ) )
            )
        );

        $result = null;
        if ( !empty( $response->body ) )
        {
            $result = $this->inputDispatcher->parse( $response );
        }
        return $result;
    }

    /**
     * instanciates a new SectionCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct
     */
    public function newSectionCreateStruct()
    {
        return new SectionCreateStruct();
    }

    /**
     * instanciates a new SectionUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct
     */
    public function newSectionUpdateStruct()
    {
        return new SectionUpdateStruct();
    }
}
