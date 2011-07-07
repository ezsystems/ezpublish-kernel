<?php
/**
 * File contains: ezp\content\tests\LocationTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content_tests
 */

namespace ezp\content\tests;

/**
 * Test case for Location class
 *
 * @package ezp
 * @subpackage content_tests
 */
use \ezp\content\Location, \ezp\content\Content;
class LocationTest extends \PHPUnit_Framework_TestCase
{
    protected $content;

    public function __construct()
    {
        parent::__construct();
        $this->setName( "Location class tests" );

        // setup a content type & content object of use by tests, fields are not needed for location
        $contentType = new \ezp\content\type\Type();
        $contentType->identifier = 'article';

        $this->content = new Content( $contentType, new \ezp\base\Locale( 'eng-GB' ) );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testChildrenWrongClass()
    {
        $location = new Location( $this->content );
        $location->children[] = \ezp\content\Section::__set_state( array( 'id' => 1 ) );
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testParentWrongClass()
    {
        $location = new Location( $this->content );
        $location->parent = \ezp\content\Section::__set_state( array( 'id' => 1 ) );
    }

    /**
     * Test that children on parent is updated when you assign a Location to children
     */
    public function testChildrenWhenSetWithParent()
    {
        $location = new Location( $this->content );
        $location2 = new Location( $this->content );
        $location2->parent = $location;
        $this->assertEquals( $location->children[0], $location2, 'Children on inverse side was not correctly updated when assigned as parent!' );
        $this->assertNotEquals( $location->children[0], new Location( $this->content ), 'Equal function miss-behaves, this should not be equal!' );
    }
}
