<?php
/**
 * File contains: ezp\Content\Tests\ContentTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use ezp\Content\Content,
    ezp\Content\Location,
    ezp\Content\Section,
    ezp\Content\Translation,
    ezp\Content\Type\Type,
    ezp\Content\Type\Field,
    ezp\Base\Locale;

/**
 * Test case for Content class
 *
 */
class ContentTest extends \PHPUnit_Framework_TestCase
{
    protected $contentType;

    protected $localeFR;
    protected $localeEN;

    public function __construct()
    {
        parent::__construct();
        $this->setName( "Content class tests" );

        // setup a content type & content object of use by tests
        $this->contentType = new Type();
        $this->contentType->identifier = 'article';

        // Add some fields
        $fields = array( '\\ezp\\Content\\Type\\Field\\String'  => array( 'title', 'ezstring', 'New Article' ),
                         '\\ezp\\Content\\Type\\Field\\Keyword' => array( 'tags',  'ezkeyword', '' ) );
        foreach ( $fields as $className => $data )
        {
            $field = new $className( $this->contentType );
            $field->identifier = $data[0];
            $field->fieldTypeString = $data[1];
            $field->default = $data[2];
            $this->contentType->fields[] = $field;
        }

        $this->localeFR = new Locale( 'fre-FR' );
        $this->localeEN = new Locale( 'eng-GB' );
    }

    /**
     * Test the default Translation internally created with a Content is created
     */
    public function testDefaultContentTranslation()
    {
        $content = new Content( $this->contentType, $this->localeEN );
        $tr = $content->translations['eng-GB'];
        self::assertEquals( count( $content->translations ), 1 );
        self::assertEquals( count( $content->versions ), 1 );
        self::assertEquals( count( $tr->versions ), 1 );
        self::assertEquals( $tr->locale->code, $this->localeEN->code );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testContentAddExistingTranslation()
    {
        $content = new Content( $this->contentType, $this->localeEN );
        $content->addTranslation( $this->localeEN );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testContentRemoveUnexistingTranslation()
    {
        $content = new Content( $this->contentType, $this->localeEN );
        $content->removeTranslation( $this->localeFR );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testContentRemoveMainLocaleTranslation()
    {
        $content = new Content( $this->contentType, $this->localeEN );
        $content->removeTranslation( $this->localeEN );
    }

    /**
     * Test that Content::removeTranslation() really removes the Translation
     * object
     */
    public function testContentRemoveTranslation()
    {
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
     */
    public function testContentAddTranslation()
    {
        $content = new Content( $this->contentType, $this->localeEN );
        $tr = $content->addTranslation( $this->localeFR );
        self::assertEquals( $tr->locale->code, $this->localeFR->code );
        self::assertEquals( count( $tr->versions ), 1 );
        self::assertEquals( count( $content->versions ), 2 );
    }

    /**
     * @expectedException ezp\Base\Exception\InvalidArgumentType
     */
    public function testLocationWrongClass()
    {
        $content = new Content( $this->contentType, new Locale( 'eng-GB' ) );
        $content->locations[] = Section::__set_state( array( 'id' => 1 ) );
    }

    /**
     * Test that foreign side of relation is updated for Location -> Content when Location is created
     */
    public function testContentLocationWhenLocationIsCreated()
    {
        $content = new Content( $this->contentType, new Locale( 'eng-GB' ) );
        $location = new Location( $content );
        $this->assertEquals( $location, $content->locations[0], 'Location on Content is not correctly updated when Location is created with content in constructor!' );
        $content->locations[] = $location;
        $this->assertEquals( 1, count( $content->locations ), 'Collection allows several instances of same object!' );
    }
}
