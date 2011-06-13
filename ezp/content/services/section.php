<?php
/**
 * File containing the ezp\Content\Services\Section class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package Content
 * @subpackages Services
 */

/**
 * Section service, used for section operations
 * @package Content
 * @subpackage Services
 */
namespace ezp\Content\Services;

use ezp\Content\Repository as ContentRepository;

class Section implements ServiceInterface
{
    public function create( \ezp\Content\Section $section )
    {
    }

    public function update( \ezp\Content\Section $section )
    {
    }

    public function load( $sectionId )
    {
    }

    public function loadByIdentifier( $sectionIdentifier )
    {
    }

    public function countAssignedContents( Section $section )
    {

    }

    public function delete( Section $section )
    {
        if ( $this->countAssignedContents( $section ) > 0 )
        {
            throw new \ezp\Content\ValidationException( 'This section is assigned to some contents' );
        }
        // do the removal
    }
}
?>
