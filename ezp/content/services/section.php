<?php
/**
 * File containing the ezp\content\Services\Section class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */

/**
 * Section service, used for section operations
 * @package ezp
 * @subpackage content
 */
namespace ezp\content\Services;
use ezp\content\Section, ezp\base\ServiceInterface, ezp\base\Repository, ezp\base\StorageEngineInterface;

class Section implements ServiceInterface
{
    /**
     * @var \ezx\base\Interfaces\Repository
     */
    protected $repository;

    /**
     * @var \ezp\base\StorageEngineInterface
     */
    protected $se;

    /**
     * Setups service with reference to repository object that created it & corresponding storage engine handler
     *
     * @param \ezp\base\Repository $repository
     * @param \ezp\base\StorageEngineInterface $se
     */
    public function __construct( Repository $repository,
                                 StorageEngineInterface $se )
    {
        $this->repository = $repository;
        $this->se = $se;
    }

    /**
     * Creates the a new Section in the content repository
     * 
     * @param \ezp\content\Section $section
     * @return \ezp\content\Section The newly create section
     * @throws \ezp\content\ValidationException If a validation problem has been found for $section
     */
    public function create( Section $section )
    {
    }

    /**
     * Updates $section in the content repository
     *
     * @param \ezp\content\Section $section
     * @return $section
     * @throws \ezp\content\ValidationException If a validation problem has been found for $section
     */
    public function update( Section $section )
    {
    }

    /**
     * Loads a Section from its id ($sectionId)
     * 
     * @param int $sectionId 
     * @return \ezp\content\Section
     * @throws \ezp\content\SectionNotFoundException if section could not be found
     */
    public function load( $sectionId )
    {
    }

    /**
     * Loads a Section from its identifier ($sectionIdentifier)
     * 
     * @param string $sectionIdentifier 
     * @return \ezp\content\Section
     * @throws \ezp\content\SectionNotFoundException if section could not be found
     */
    public function loadByIdentifier( $sectionIdentifier )
    {
    }

    /**
     * Counts the contents which $section is assigned to 
     * 
     * @param \ezp\content\Section $section
     * @return int
     */
    public function countAssignedContents( Section $section )
    {
    }

    /**
     * Deletes $section from content repository 
     * 
     * @param \ezp\content\Section $section
     * @return void
     * @throws \ezp\content\ValidationException
     *         if section can be deleted
     *         because it is still assigned to some contents.
     */
    public function delete( Section $section )
    {
        if ( $this->countAssignedContents( $section ) > 0 )
        {
            throw new \ezp\content\ValidationException( 'This section is assigned to some contents' );
        }
        // do the removal
    }
}
?>
