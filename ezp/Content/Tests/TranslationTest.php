<?php
/**
 * File contains: ezp\Content\Tests\ContentTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;

/**
 * Test case for Translation class
 *
 */
use \ezp\Content\Content, \ezp\Content\Translation;
class TranslationTest extends \PHPUnit_Framework_TestCase
{
    protected $contentType;

    protected $localeFR;

    protected $localeEN;

    public function __construct()
    {
        parent::__construct();
        $this->setName( "Translation class tests" );

        $this->contentType = new \ezp\Content\Type\Type();
        $this->contentType->identifier = 'article';

        $this->localeEN = new \ezp\Base\Locale( 'eng-GB' );
        $this->localeFR = new \ezp\Base\Locale( 'fre-FR' );
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
