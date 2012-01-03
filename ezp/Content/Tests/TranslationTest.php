<?php
/**
 * File contains: ezp\Content\Tests\ContentTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use ezp\Content,
    ezp\Content\Translation,
    ezp\Content\Type\Concrete as ConcreteType;

/**
 * Test case for Translation class
 *
 */
class TranslationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Content\Type
     */
    protected $contentType;

    public function setUp()
    {
        parent::setUp();

        $this->contentType = new ConcreteType();
        $this->contentType->identifier = 'article';
    }

    /**
     * @expectedException DomainException
     * @FIXME Use "@covers"
     */
    public function testTranslationFields()
    {
        $this->markTestIncomplete( '@TODO: Re impl' );
        $tr = new Translation( $this->localeFR, new Content( $this->contentType, $this->localeEN ) );
        $fields = $tr->fields;
    }

    /**
     * @expectedException DomainException
     * @FIXME Use "@covers"
     */
    public function testTranslationLast()
    {
        $this->markTestIncomplete( '@TODO: Re impl' );
        $tr = new Translation( $this->localeFR, new Content( $this->contentType, $this->localeEN ) );
        $last = $tr->last;
    }

    /**
     * Test that current version is null in a new Translation
     * @FIXME Use "@covers"
     */
    public function testTranslationCurrent()
    {
        $this->markTestIncomplete( '@TODO: Re impl' );
        $tr = new Translation( $this->localeFR, new Content( $this->contentType, $this->localeEN ) );
        self::assertEquals( $tr->current, null );
    }

    /**
     * Test the locale code is right in a new Translation
     * @FIXME Use "@covers"
     */
    public function testTranslationLocaleCode()
    {
        $this->markTestIncomplete( '@TODO: Re impl' );
        $tr = new Translation( $this->localeFR, new Content( $this->contentType, $this->localeEN ) );
        self::assertEquals( $tr->localeCode, 'fre-FR' );
    }

    /**
     * Test that Translation::createNewVersion() adds a new version
     * @covers \ezp\Content\Translation::createNewVersion
     */
    public function testTranslationCreateVersion()
    {
        $this->markTestIncomplete( '@TODO: Re impl' );
        $tr = new Translation( $this->localeFR, new Content( $this->contentType, $this->localeEN ) );
        $version = $tr->createNewVersion();
        self::assertEquals( count( $tr->versions ), 1 );
        self::assertEquals( $tr->last->locale->code, $this->localeFR->code );
    }

}
