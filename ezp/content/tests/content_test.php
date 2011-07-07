<?php
/**
 * File contains: ezp\content\tests\ContentTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content_tests
 */

namespace ezp\content\tests;

/**
 * Test case for Content class
 *
 * @package ezp
 * @subpackage content_tests
 */
use \ezp\content\Content, \ezp\content\Location;
class ContentTest extends \PHPUnit_Framework_TestCase
{
    protected $contentType;

    public function __construct()
    {
        parent::__construct();
        $this->setName( "Content class tests" );

        // setup a content type & content object of use by tests
        $this->contentType = new \ezp\content\type\Type();
        $this->contentType->identifier = 'article';

        // Add some fields
        $fields = array( 'title' => 'ezstring', 'tags' => 'ezkeyword' );
        foreach ( $fields as $identifier => $fieldTypeString )
        {
            $field = new \ezp\content\type\Field( $this->contentType );
            $field->identifier = $identifier;
            $field->fieldTypeString = $fieldTypeString;
            $this->contentType->fields[] = $field;
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testLocationWrongClass()
    {
        $content = new Content( $this->contentType, new \ezp\base\Locale( 'eng-GB' ) );
        $content->locations[] = \ezp\content\Section::__set_state( array( 'id' => 1 ) );
    }

    /**
     * Test that foreign side of relation is updated for Location -> Content when Location is created
     */
    public function testContentLocationWhenLocationIsCreated()
    {
        $content = new Content( $this->contentType, new \ezp\base\Locale( 'eng-GB' ) );
        $location = new Location( $content );
        $this->assertEquals( $location, $content->locations[0], 'Location on Content is not correctly updated when Location is created with content in constructor!' );
        $content->locations[] = $location;
        $this->assertEquals( 1, count( $content->locations ), 'Collection allows several instances of same object!' );
    }
}
