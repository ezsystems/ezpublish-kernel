<?php
/**
 * File contains: ezp\Content\Tests\LocationTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use ezp\Content\Location\Concrete as ConcreteLocation,
    ezp\Content\Concrete as ConcreteContent,
    ezp\Content\Section\Concrete as ConcreteSection,
    ezp\Content\Type\Concrete as ConcreteType,
    ezp\Base\ServiceContainer,
    ezp\User\Proxy as ProxyUser,
    PHPUnit_Framework_TestCase;

/**
 * Test case for Location class
 *
 */
class LocationTest extends PHPUnit_Framework_TestCase
{
    protected $content;

    public function setUp()
    {
        parent::setUp();

        // setup a content type & content object of use by tests, fields are not needed for location
        $contentType = new ConcreteType();
        $contentType->identifier = 'article';

        $sc = new ServiceContainer;
        $this->content = new ConcreteContent( $contentType, new ProxyUser( 10, $sc->getRepository()->getUserService() ) );
    }

    /**
     * @expectedException ezp\Base\Exception\InvalidArgumentType
     * @FIXME Use "@covers"
     */
    public function testChildrenWrongClass()
    {
        $location = new ConcreteLocation( $this->content );
        $location->children[] = new ConcreteSection();
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     * @FIXME Use "@covers"
     */
    public function testParentWrongClass()
    {
        $location = new ConcreteLocation( $this->content );
        $location->setParent( new ConcreteSection() );
    }

    /**
     * Test that children on parent is updated when you assign a Location to children
     * @FIXME Use "@covers"
     */
    public function testChildrenWhenSetWithParent()
    {
        $location = new ConcreteLocation( $this->content );
        $location2 = new ConcreteLocation( $this->content );
        $location2->setParent( $location );
        $this->assertEquals( $location->children[0], $location2, 'Children on inverse side was not correctly updated when assigned as parent!' );
        $this->assertNotEquals( $location->children[0], new ConcreteLocation( $this->content ), 'Equal function miss-behaves, this should not be equal!' );
    }
}
