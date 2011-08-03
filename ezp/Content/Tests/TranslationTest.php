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
    ezp\Content\Translation,
    ezp\Content\Type,
    ezp\Base\Locale;

/**
 * Test case for Translation class
 *
 */
class TranslationTest extends \PHPUnit_Framework_TestCase
{
    protected $contentType;

    protected $localeFR;

    protected $localeEN;

    public function setUp()
    {
        parent::setUp();

        $this->contentType = new Type();
        $this->contentType->identifier = 'article';

        $this->localeEN = new Locale( 'eng-GB' );
        $this->localeFR = new Locale( 'fre-FR' );
    }

    /**
     * @expectedException DomainException
     */
    public function testTranslationFields()
    {
        $tr = new Translation( $this->localeFR, new Content( $this->contentType, $this->localeEN ) );
        $fields = $tr->fields;
    }

    /**
     * @expectedException DomainException
     */
    public function testTranslationLast()
    {
        $tr = new Translation( $this->localeFR, new Content( $this->contentType, $this->localeEN ) );
        $last = $tr->last;
    }

    /**
     * Test that current version is null in a new Translation
     */
    public function testTranslationCurrent()
    {
        $tr = new Translation( $this->localeFR, new Content( $this->contentType, $this->localeEN ) );
        self::assertEquals( $tr->current, null );
    }

    /**
     * Test the locale code is right in a new Translation
     */
    public function testTranslationLocaleCode()
    {
        $tr = new Translation( $this->localeFR, new Content( $this->contentType, $this->localeEN ) );
        self::assertEquals( $tr->localeCode, 'fre-FR' );
    }

    /**
     * Test that Translation::createNewVersion() adds a new version
     */
    public function testTranslationCreateVersion()
    {
        $tr = new Translation( $this->localeFR, new Content( $this->contentType, $this->localeEN ) );
        $version = $tr->createNewVersion();
        self::assertEquals( count( $tr->versions ), 1 );
        self::assertEquals( $tr->last->locale->code, $this->localeFR->code );
    }

}
