<?php
/**
 * @package eZ\Publish\Core\Repository
 */
namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct,
    eZ\Publish\API\Repository\Values\Content\ContentInfo,
    eZ\Publish\API\Repository\Values\Content\Section,
    eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct,

    eZ\Publish\API\Repository\SectionService as SectionServiceInterface,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\SPI\Persistence\Handler,

    eZ\Publish\SPI\Persistence\Content\Section as SPISection,

    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\Core\Base\Exceptions\BadStateException,
    eZ\Publish\API\Repository\Exceptions\NotFoundException,
    eZ\Publish\API\Repository\Exceptions\UnauthorizedException;

/**
 * Section service, used for section operations
 *
 * @package eZ\Publish\Core\Repository
 */
class SectionService implements SectionServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository  $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $handler, array $settings = array() )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
        $this->settings = $settings;
    }

    /**
     * Creates a new Section in the content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create a section
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the new identifier in $sectionCreateStruct already exists
     *
     * @param \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct $sectionCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section The newly created section
     */
    public function createSection( SectionCreateStruct $sectionCreateStruct )
    {
        if ( !is_string( $sectionCreateStruct->name ) || empty( $sectionCreateStruct->name ) )
            throw new InvalidArgumentValue( "name", $sectionCreateStruct->name, "SectionCreateStruct" );

        if ( !is_string( $sectionCreateStruct->identifier ) || empty( $sectionCreateStruct->identifier ) )
            throw new InvalidArgumentValue( "identifier", $sectionCreateStruct->identifier, "SectionCreateStruct" );

        try
        {
            $existingSection = $this->loadSectionByIdentifier( $sectionCreateStruct->identifier );
            if ( $existingSection !== null )
                throw new InvalidArgumentException( "sectionCreateStruct", "section with specified identifier already exists" );
        }
        catch ( NotFoundException $e ) {}

        $spiSection = $this->persistenceHandler->sectionHandler()->create(
            $sectionCreateStruct->name,
            $sectionCreateStruct->identifier
        );

        return $this->buildDomainSectionObject( $spiSection );
    }

    /**
     * Updates the given section in the content repository
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
        if ( !is_numeric( $section->id ) )
            throw new InvalidArgumentValue( "id", $section->id, "Section" );

        if ( $sectionUpdateStruct->name !== null && !is_string( $sectionUpdateStruct->name ) )
            throw new InvalidArgumentValue( "name", $section->name, "Section" );

        if ( $sectionUpdateStruct->identifier !== null && !is_string( $sectionUpdateStruct->identifier ) )
            throw new InvalidArgumentValue( "identifier", $section->identifier, "Section" );

        if ( $sectionUpdateStruct->identifier !== null )
        {
            try
            {
                $existingSection = $this->loadSectionByIdentifier( $sectionUpdateStruct->identifier );
                if ( $existingSection !== null )
                    throw new InvalidArgumentException( "sectionUpdateStruct", "section with specified identifier already exists" );
            }
            catch ( NotFoundException $e ) {}
        }

        $loadedSection = $this->loadSection( $section->id );

        $spiSection = $this->persistenceHandler->sectionHandler()->update(
            $loadedSection->id,
            $sectionUpdateStruct->name ?: $loadedSection->name,
            $sectionUpdateStruct->identifier ?: $loadedSection->identifier
        );

        return $this->buildDomainSectionObject( $spiSection );
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
        if ( !is_numeric( $sectionId ) )
            throw new InvalidArgumentValue( "sectionId", $sectionId );

        $spiSection = $this->persistenceHandler->sectionHandler()->load( $sectionId );
        return $this->buildDomainSectionObject( $spiSection );
    }

    /**
     * Loads all sections
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read a section
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section[]
     */
    public function loadSections()
    {
        $spiSections = $this->persistenceHandler->sectionHandler()->loadAll();

        $sections = array();
        foreach ( $spiSections as $spiSection )
        {
            $sections[] = $this->buildDomainSectionObject( $spiSection );
        }

        return $sections;
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
        if ( !is_string( $sectionIdentifier ) || empty( $sectionIdentifier ) )
            throw new InvalidArgumentValue( "sectionIdentifier", $sectionIdentifier );

        $spiSection = $this->persistenceHandler->sectionHandler()->loadByIdentifier( $sectionIdentifier );
        return $this->buildDomainSectionObject( $spiSection );
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
        if ( !is_numeric( $section->id ) )
            throw new InvalidArgumentValue( "id", $section->id, "Section" );

        return $this->persistenceHandler->sectionHandler()->assignmentsCount( $section->id );
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
        if ( !is_numeric( $contentInfo->contentId ) )
            throw new InvalidArgumentValue( "contentId", $contentInfo->contentId, "ContentInfo" );

        if ( !is_numeric( $section->id ) )
            throw new InvalidArgumentValue( "id", $section->id, "Section" );

        $loadedContentInfo = $this->repository->getContentService()->loadContentInfo( $contentInfo->contentId );
        $loadedSection = $this->loadSection( $section->id );

        $this->persistenceHandler->sectionHandler()->assign(
            $loadedSection->id,
            $loadedContentInfo->contentId
        );
    }

    /**
     * Deletes $section from content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified section is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to delete a section
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If section can not be deleted
     *         because it is still assigned to some contents.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     */
    public function deleteSection( Section $section )
    {
        if ( !is_numeric( $section->id ) )
            throw new InvalidArgumentValue( "id", $section->id, "Section" );

        $loadedSection = $this->loadSection( $section->id );

        if ( $this->countAssignedContents( $loadedSection ) > 0 )
            throw new BadStateException( "section", 'section is still assigned to content' );

        $this->persistenceHandler->sectionHandler()->delete( $loadedSection->id );
    }

    /**
     * instantiates a new SectionCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct
     */
    public function newSectionCreateStruct()
    {
        return new SectionCreateStruct();
    }

    /**
     * instantiates a new SectionUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct
     */
    public function newSectionUpdateStruct()
    {
        return new SectionUpdateStruct();
    }

    /**
     * Builds API Section object from provided SPI Section object
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Section $spiSection
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    protected function buildDomainSectionObject( SPISection $spiSection )
    {
        return new Section(
            array(
                'id'         => $spiSection->id,
                'identifier' => $spiSection->identifier,
                'name'       => $spiSection->name
            )
        );
    }
}
