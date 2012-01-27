<?php
/**
 * @package ezp\Publish\PublicAPI
 */
namespace ezp\Publish\PublicAPI\Content;

use ezp\PublicAPI\Values\Content\SectionCreateStruct;

use ezp\Base\Exception\NotFound;
use ezp\Base\Exception\InvalidArgumentValue;
use ezp\PublicAPI\Values\Content\Content;
use ezp\PublicAPI\Values\Content\ContentInfo;
use ezp\PublicAPI\Values\Content\Section;
use ezp\PublicAPI\Values\Content\Location;
use ezp\PublicAPI\Values\Content\SectionUpdateStruct;
use ezp\PublicAPI\Interfaces\SectionService as SectionServiceInterface;
use ezp\Persistence\Handler;
use ezp\PublicAPI\Interfaces\Repository as RepositoryInterface;

/**
 * Section service, used for section operations
 *
 * @package ezp\Publish\PublicAPI
 */
class SectionService implements SectionServiceInterface
{
    /**
     * @var \ezp\PublicAPI\Interfaces\Repository
     */
    protected $repository;

    /**
     * @var \ezp\Persistence\Handler
     */
    protected $handler;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \ezp\PublicAPI\Interfaces\Repository $repository
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
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user user is not allowed to create a section
     * @throws ezp\PublicAPI\Interfaces\IllegalArgumentException If the new identifier in $sectionCreateStruct already exists
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
        catch ( NotFound $e ) {}

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
     * @throws ezp\PublicAPI\Interfaces\NotFoundException if section could not be found
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user user is not allowed to create a section
     * @throws ezp\PublicAPI\Interfaces\IllegalArgumentException If the new identifier already exists (if set in the update struct)
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
            catch ( NotFound $e ) {}
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
     * @throws ezp\PublicAPI\Interfaces\NotFoundException if section could not be found
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user user is not allowed to read a section
     */
    public function loadSection( $sectionId )
    {
        $sectionArray = (array) $this->handler->sectionHandler()->load( $sectionId );

        if ( $sectionArray === null )
            throw new NotFound( "Section", $sectionId );

        return new Section( $sectionArray );
    }

    /**
     * Loads all sections
     *
     * @return array of {@link Section}
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user user is not allowed to read a section
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
     * @throws ezp\PublicAPI\Interfaces\NotFoundException if section could not be found
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user user is not allowed to read a section
     */
    public function loadSectionByIdentifier( $sectionIdentifier ){}

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
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If user does not have access to view provided object
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
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user is not allowed to assign a section to the starting point
     *
     */
    public function assignSectionToSubTree( Location $startingPoint, Section $section ){}

    /**
     * Deletes $section from content repository
     *
     * @param Section $section
     *
     * @throws ezp\PublicAPI\Interfaces\NotFoundException If the specified section is not found
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user user is not allowed to delete a section
     * @throws ezp\PublicAPI\Interfaces\BadStateException  if section can not be deleted
     *         because it is still assigned to some contents.
     */
    public function deleteSection( Section $section )
    {
        $loadedSection = $this->loadSection( $section->id );
        if ( $loadedSection === null )
            throw new NotFound( "Section", $section->id );

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
