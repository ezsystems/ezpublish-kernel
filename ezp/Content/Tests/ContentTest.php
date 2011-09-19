<?php
/**
 * File contains: ezp\Content\Tests\ContentTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use ezp\Content,
    ezp\Content\Location,
    ezp\Content\Section,
    ezp\Content\Type,
    ezp\Content\Type\FieldDefinition,
    ezp\User,
    ezp\Content\FieldType\Value as FieldValue,
    ezp\Content\FieldType\TextLine\Value as TextLineValue,
    ezp\Content\FieldType\Keyword\Value as KeywordValue;

/**
 * Test case for Content class
 *
 */
class ContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Content\Type
     */
    protected $contentType;

    public function setUp()
    {
        parent::setUp();

        // setup a content type & content object of use by tests
        $this->contentType = new Type();
        $this->contentType->identifier = 'article';

        // Add some fields
        $fieldsData = array(
            'title' => array( 'ezstring', new TextLineValue( 'New Article' ) ),
            'tags' => array( 'ezkeyword', new KeywordValue() )
        );
        $fields = $this->contentType->getFields();
        foreach ( $fieldsData as $identifier => $data )
        {
            $field = new FieldDefinition( $this->contentType, $data[0] );
            $field->identifier = $identifier;
            $field->setDefaultValue( $data[1] );
            $fields[] = $field;
        }
    }

    /**
     * Test the default Translation internally created with a Content is created
     * @covers \ezp\Content::__construct
     */
    public function testDefaultContentTranslation()
    {
        $this->markTestIncomplete( '@TODO: Re impl' );
        $content = new Content( $this->contentType, $this->localeEN );
        $tr = $content->translations['eng-GB'];
        self::assertEquals( 1, count( $content->translations ) );
        self::assertEquals( 1, count( $content->versions ) );
        self::assertEquals( 1, count( $tr->versions ) );
        self::assertEquals( $tr->locale->code, $this->localeEN->code );
    }

    /**
     * @expectedException InvalidArgumentException
     * @covers \ezp\Content::addTranslation
     */
    public function testContentAddExistingTranslation()
    {
        $this->markTestIncomplete( '@TODO: Re impl' );
        $content = new Content( $this->contentType, $this->localeEN );
        $content->addTranslation( $this->localeEN );
    }

    /**
     * @expectedException InvalidArgumentException
     * @covers \ezp\Content::removeTranslation
     */
    public function testContentRemoveUnexistingTranslation()
    {
        $this->markTestIncomplete( '@TODO: Re impl' );
        $content = new Content( $this->contentType, $this->localeEN );
        $content->removeTranslation( $this->localeFR );
    }

    /**
     * @expectedException InvalidArgumentException
     * @covers \ezp\Content::removeTranslation
     */
    public function testContentRemoveMainLocaleTranslation()
    {
        $this->markTestIncomplete( '@TODO: Re impl' );
        $content = new Content( $this->contentType, $this->localeEN );
        $content->removeTranslation( $this->localeEN );
    }

    /**
     * Test that Content::removeTranslation() really removes the Translation
     * object
     * @covers \ezp\Content::removeTranslation
     */
    public function testContentRemoveTranslation()
    {
        $this->markTestIncomplete( '@TODO: Re impl' );
        $content = new Content( $this->contentType, $this->localeEN );
        $content->addTranslation( $this->localeFR );
        $content->removeTranslation( $this->localeFR );
        self::assertEquals( count( $content->translations ), 1 );
    }

    /**
     * Test Content::addTranslation() behaviour:
     * - new Translation has the right locale
     * - new Translation has one version
     * - a new version is also added to the Content
     * @covers \ezp\Content::addTranslation
     */
    public function testContentAddTranslation()
    {
        $this->markTestIncomplete( '@TODO: Re impl' );
        $content = new Content( $this->contentType, $this->localeEN );
        $tr = $content->addTranslation( $this->localeFR );
        self::assertEquals( $tr->locale->code, $this->localeFR->code );
        self::assertEquals( 1, count( $tr->versions ) );
        self::assertEquals( 2, count( $content->versions ) );
    }

    /**
     * @covers \ezp\Content::getLocations
     * @expectedException ezp\Base\Exception\InvalidArgumentType
     */
    public function testLocationWrongClass()
    {
        $content = new Content( $this->contentType, new User( 10 ) );
        $content->locations[] = new Section();
    }

    /**
     * Test that foreign side of relation is updated for Location -> Content when Location is created
     *
     * @covers \ezp\Content::getLocations
     */
    public function testContentLocationWhenLocationIsCreated()
    {
        $content = new Content( $this->contentType, new User( 10 ) );
        $location = new Location( $content );
        $locations = $content->getLocations();
        $this->assertEquals( $location, $locations[0], 'Location on Content is not correctly updated when Location is created with content in constructor!' );
        $locations[] = $location;
        $this->assertEquals( 1, count( $content->getLocations() ), 'Collection allows several instances of same object!' );
    }
}
