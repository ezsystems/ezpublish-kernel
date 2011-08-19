<?php
/**
 * File contains: ezp\Content\Tests\LocationTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use ezp\Content\Location,
    ezp\Content,
    ezp\Content\Section,
    ezp\Content\Type;

/**
 * Test case for Location class
 *
 */
class LocationTest extends \PHPUnit_Framework_TestCase
{
    protected $content;

    public function setUp()
    {
        parent::setUp();

        // setup a content type & content object of use by tests, fields are not needed for location
        $contentType = new Type();
        $contentType->identifier = 'article';

        $this->content = new Content( $contentType );
    }

    /**
     * @expectedException ezp\Base\Exception\InvalidArgumentType
     * @FIXME Use "@covers"
     */
    public function testChildrenWrongClass()
    {
        $location = new Location( $this->content );
        $location->children[] = new Section();
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     * @FIXME Use "@covers"
     */
    public function testParentWrongClass()
    {
        $location = new Location( $this->content );
        $location->parent = new Section();
    }

    /**
     * Test that children on parent is updated when you assign a Location to children
     * @FIXME Use "@covers"
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
