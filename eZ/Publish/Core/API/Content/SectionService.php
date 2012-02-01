<?php
/**
 * @package eZ\Publish\Core\API
 */
namespace eZ\Publish\Core\API\Content;

use eZ\Publish\API\Values\Content\SectionCreateStruct;

use eZ\Publish\Core\Base\Exception\NotFound;
use eZ\Publish\Core\Base\Exception\InvalidArgumentValue;
use ezp\Base\Exception\InvalidArgumentValue as PersistenceInvalidArgumentValue;
use ezp\Base\Exception\NotFound as PersistenceNotFound;
use eZ\Publish\API\Values\Content\Content;
use eZ\Publish\API\Values\Content\ContentInfo;
use eZ\Publish\API\Values\Content\Section;
use eZ\Publish\API\Values\Content\Location;
use eZ\Publish\API\Values\Content\SectionUpdateStruct;
use eZ\Publish\API\Interfaces\SectionService as SectionServiceInterface;
use ezp\Persistence\Handler;
use eZ\Publish\API\Interfaces\Repository as RepositoryInterface;

/**
 * Section service, used for section operations
 *
 * @package eZ\Publish\Core\API
 */
class SectionService implements SectionServiceInterface
{
    /**
     * @var \eZ\Publish\API\Interfaces\Repository
     */
    protected $repository;

    /**
     * @var \ezp\Persistence\Handler
     */
    protected $handler;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Interfaces\Repository $repository
     * @param \ezp\Persistence\Handler $handler
     */
    public function __construct( RepositoryInterface $repository, Handler $handler )
    {
        $this->repository = $repository;
        $this->handler = $handler;
    }

    /**
     * Creates the a new Section in the content repository
     *
     * @param SectionCreateStruct $sectionCreateStruct
     *
     * @return Section The newly create section
     *
     * @throws eZ\Publish\API\Interfaces\UnauthorizedException If the current user user is not allowed to create a section
     * @throws eZ\Publish\API\Interfaces\IllegalArgumentException If the new identifier in $sectionCreateStruct already exists
     */
    public function createSection( SectionCreateStruct $sectionCreateStruct )
    {
        if ( $sectionCreateStruct->name === null )
            throw new InvalidArgumentValue( "name", "null" );

        if ( $sectionCreateStruct->identifier === null )
            throw new InvalidArgumentValue( "identifier", "null" );

        try
        {
            $existingSection = $this->handler->sectionHandler()->loadByIdentifier( $sectionCreateStruct->identifier );
            if ( $existingSection !== null )
                throw new IllegalArgumentException( "identifer", $sectionCreateStruct->identifier );
        }
        catch ( PersistenceNotFound $e ) {}

        $createdSection = (array) $this->handler->sectionHandler()->create(
            $sectionCreateStruct->name,
            $sectionCreateStruct->identifier
        );

        return new Section( $createdSection );
    }

    /**
     * Updates the given in the content repository
     *
     * @param Section $section
     * @param SectionUpdateStruct $sectionUpdateStruct
     *
     * @return Section
     *
     * @throws eZ\Publish\API\Interfaces\NotFoundException if section could not be found
     * @throws eZ\Publish\API\Interfaces\UnauthorizedException If the current user user is not allowed to create a section
     * @throws eZ\Publish\API\Interfaces\IllegalArgumentException If the new identifier already exists (if set in the update struct)
     */
    public function updateSection( Section $section, SectionUpdateStruct $sectionUpdateStruct )
    {
        if ( $sectionUpdateStruct->identifier !== null )
        {
            try
            {
                $existingSection = $this->handler->sectionHandler()->loadByIdentifier( $sectionUpdateStruct->identifier );
                if ( $existingSection !== null )
                    throw new IllegalArgumentException( "identifer", $sectionUpdateStruct->identifier );
            }
            catch ( PersistenceNotFound $e ) {}
        }

        $section = $this->loadSection( $section->id );
        if ( $section === null )
            throw new NotFound( "Section", $section->id );

        $updatedSection = (array) $this->handler->sectionHandler()->update(
            $section->id,
            $sectionUpdateStruct->name !== null ? $sectionUpdateStruct->name : $section->name,
            $sectionUpdateStruct->identifier !== null ? $sectionUpdateStruct->identifier : $section->identifier
        );

        if ( $updatedSection === null )
            throw new NotFound( "Section", $section->id );

        return new Section( $updatedSection );
    }

    /**
     * Loads a Section from its id ($sectionId)
     *
     * @param int $sectionId
     *
     * @return Section
     *
     * @throws eZ\Publish\API\Interfaces\NotFoundException if section could not be found
     * @throws eZ\Publish\API\Interfaces\UnauthorizedException If the current user user is not allowed to read a section
     */
    public function loadSection( $sectionId )
    {
        try
        {
            $sectionArray = (array) $this->handler->sectionHandler()->load( $sectionId );
        }
        catch ( PersistenceNotFound $e )
        {
            throw new NotFound( "Section", $sectionId, $e );
        }

        return new Section( $sectionArray );
    }

    /**
     * Loads all sections
     *
     * @return array of {@link Section}
     *
     * @throws eZ\Publish\API\Interfaces\UnauthorizedException If the current user user is not allowed to read a section
     */
    public function loadSections()
    {
        $returnArray = array();

        $allSections = $this->handler->sectionHandler()->loadAll();
        if ( is_array( $allSections ) )
        {
            foreach ( $allSections as $section )
            {
                $returnArray[] = new Section( (array) $section );
            }
        }
        else if ( $allSections !== null )
        {
            $returnArray[] = new Section( (array) $allSections );
        }

        return $returnArray;
    }

    /**
     * Loads a Section from its identifier ($sectionIdentifier)
     *
     * @param string $sectionIdentifier
     *
     * @return Section
     *
     * @throws eZ\Publish\API\Interfaces\NotFoundException if section could not be found
     * @throws eZ\Publish\API\Interfaces\UnauthorizedException If the current user user is not allowed to read a section
     */
    public function loadSectionByIdentifier( $sectionIdentifier )
    {
        $sectionArray = (array) $this->handler->sectionHandler()->loadByIdentifier( $sectionIdentifier );
        if ( $sectionArray === null )
            throw new NotFound( "Section", $sectionIdentifier );

        return new Section( $sectionArray );
    }

    /**
     * Counts the contents which $section is assigned to
     *
     * @param Section $section
     *
     * @return int
     */
    public function countAssignedContents( Section $section )
    {
        return $this->handler->sectionHandler()->assignmentsCount( $section->id );
    }

    /**
     * assigns the content to the given section
     * this method overrides the current assigned section
     *
     * @param ContentInfo $contentInfo
     * @param Section $section
     *
     * @throws eZ\Publish\API\Interfaces\UnauthorizedException If user does not have access to view provided object
     */
    public function assignSection( ContentInfo $contentInfo, Section $section ){}

    /**
     * Assigns $section to the contents held by $startingPoint location and
     * all contents held by descendants locations of $startingPoint to which the user has
     * the permission to assign a section
     *
     * @param Location $startingPoint
     * @param Section $section
     *
     * @return array  a list (string) of descendants which are not changed due to permissions
     *
     * @throws eZ\Publish\API\Interfaces\UnauthorizedException If the current user is not allowed to assign a section to the starting point
     *
     */
    public function assignSectionToSubTree( Location $startingPoint, Section $section ){}

    /**
     * Deletes $section from content repository
     *
     * @param Section $section
     *
     * @throws eZ\Publish\API\Interfaces\NotFoundException If the specified section is not found
     * @throws eZ\Publish\API\Interfaces\UnauthorizedException If the current user user is not allowed to delete a section
     * @throws eZ\Publish\API\Interfaces\BadStateException  if section can not be deleted
     *         because it is still assigned to some contents.
     */
    public function deleteSection( Section $section )
    {
        try
        {
            $this->handler->sectionHandler()->load( $section->id );
        }
        catch ( PersistenceNotFound $e )
        {
            throw new NotFound( "Section", $section->id, $e );
        }

        $this->handler->sectionHandler()->delete( $section->id );
    }

    /**
     * instanciates a new SectionCreateStruct
     * 
     * @return SectionCreateStruct
     */
    public function newSectionCreateStruct(){}
    
    /**
     * instanciates a new SectionUpdateStruct
     * 
     * @return SectionUpdateStruct
     */
    public function newSectionUpdateStruct(){}
}
