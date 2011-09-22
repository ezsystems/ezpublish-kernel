<?php
/**
 * File contains: ezp\Content\Tests\ContentTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use ezp\Content\Concrete as ConcreteContent,
    ezp\Content\Location\Concrete as ConcreteLocation,
    ezp\Content\Section\Concrete as ConcreteSection,
    ezp\User\Proxy as ProxyUser;

/**
 * Test case for Content class
 *
 */
class ContentTest extends BaseContentTest
{
    /**
     * Test the default Translation internally created with a Content is created
     * @covers \ezp\Content\Concrete::__construct
     */
    public function testDefaultContentTranslation()
    {
        $this->markTestIncomplete( '@TODO: Re impl' );
        $content = new ConcreteContent( $this->contentType, $this->localeEN );
        $tr = $content->translations['eng-GB'];
        self::assertEquals( 1, count( $content->translations ) );
        self::assertEquals( 1, count( $content->versions ) );
        self::assertEquals( 1, count( $tr->versions ) );
        self::assertEquals( $tr->locale->code, $this->localeEN->code );
    }

    /**
     * @expectedException InvalidArgumentException
     * @covers \ezp\Content\Concrete::addTranslation
     */
    public function testContentAddExistingTranslation()
    {
        $this->markTestIncomplete( '@TODO: Re impl' );
        $content = new ConcreteContent( $this->contentType, $this->localeEN );
        $content->addTranslation( $this->localeEN );
    }

    /**
     * @expectedException InvalidArgumentException
     * @covers \ezp\Content\Concrete::removeTranslation
     */
    public function testContentRemoveUnexistingTranslation()
    {
        $this->markTestIncomplete( '@TODO: Re impl' );
        $content = new ConcreteContent( $this->contentType, $this->localeEN );
        $content->removeTranslation( $this->localeFR );
    }

    /**
     * @expectedException InvalidArgumentException
     * @covers \ezp\Content\Concrete::removeTranslation
     */
    public function testContentRemoveMainLocaleTranslation()
    {
        $this->markTestIncomplete( '@TODO: Re impl' );
        $content = new ConcreteContent( $this->contentType, $this->localeEN );
        $content->removeTranslation( $this->localeEN );
    }

    /**
     * Test that Content::removeTranslation() really removes the Translation
     * object
     * @covers \ezp\Content\Concrete::removeTranslation
     */
    public function testContentRemoveTranslation()
    {
        $this->markTestIncomplete( '@TODO: Re impl' );
        $content = new ConcreteContent( $this->contentType, $this->localeEN );
        $content->addTranslation( $this->localeFR );
        $content->removeTranslation( $this->localeFR );
        self::assertEquals( count( $content->translations ), 1 );
    }

    /**
     * Test Content::addTranslation() behaviour:
     * - new Translation has the right locale
     * - new Translation has one version
     * - a new version is also added to the Content
     * @covers \ezp\Content\Concrete::addTranslation
     */
    public function testContentAddTranslation()
    {
        $this->markTestIncomplete( '@TODO: Re impl' );
        $content = new ConcreteContent( $this->contentType, $this->localeEN );
        $tr = $content->addTranslation( $this->localeFR );
        self::assertEquals( $tr->locale->code, $this->localeFR->code );
        self::assertEquals( 1, count( $tr->versions ) );
        self::assertEquals( 2, count( $content->versions ) );
    }

    /**
     * @covers \ezp\Content\Concrete::getLocations
     * @expectedException ezp\Base\Exception\InvalidArgumentType
     */
    public function testLocationWrongClass()
    {
        $content = new ConcreteContent( $this->contentType, new ProxyUser( 10, $this->repository->getUserService() ) );
        $content->locations[] = new ConcreteSection();
    }

    /**
     * Test that foreign side of relation is updated for Location -> Content when Location is created
     *
     * @covers \ezp\Content\Concrete::getLocations
     */
    public function testContentLocationWhenLocationIsCreated()
    {
        $content = new ConcreteContent( $this->contentType, new ProxyUser( 10, $this->repository->getUserService() ) );
        $location = new ConcreteLocation( $content );
        $locations = $content->getLocations();
        $this->assertEquals( $location, $locations[0], 'Location on Content is not correctly updated when Location is created with content in constructor!' );
        $locations[] = $location;
        $this->assertEquals( 1, count( $content->getLocations() ), 'Collection allows several instances of same object!' );
    }
}
