<?php
/**
 * File containing the ContentHelper class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Helper\Tests;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Repository\DomainLogic\Values\Content\Content;
use eZ\Publish\Core\Repository\DomainLogic\Values\Content\VersionInfo;
use PHPUnit_Framework_TestCase;
use eZ\Publish\Core\Helper\TranslationHelper;

class TranslationHelperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\API\Repository\ContentService
     */
    private $contentService;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    private $contentHelper;

    /**
     * @var Field[]
     */
    private $translatedFields;

    /**
     * @var string[]
     */
    private $translatedNames;

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' );
        $this->contentService = $this->getMock( 'eZ\\Publish\\API\\Repository\\ContentService' );
        $this->contentHelper = new TranslationHelper( $this->configResolver, $this->contentService );
        $this->translatedNames = array(
            'eng-GB' => 'My name in english',
            'fre-FR' => 'Mon nom en français',
            'esl-ES' => 'Mi nombre en español',
            'heb-IL' => 'השם שלי בעברית',
        );
        $this->translatedFields = array(
            'eng-GB' => new Field( array( 'value' => 'Content in english', 'fieldDefIdentifier' => 'test', 'languageCode' => 'eng-GB' ) ),
            'fre-FR' => new Field( array( 'value' => 'Contenu en français', 'fieldDefIdentifier' => 'test', 'languageCode' => 'fre-FR' ) ),
            'esl-ES' => new Field( array( 'value' => 'Contenido en español', 'fieldDefIdentifier' => 'test', 'languageCode' => 'esl-ES' ) ),
            'heb-IL' => new Field( array( 'value' => 'תוכן בספרדית', 'fieldDefIdentifier' => 'test', 'languageCode' => 'heb-IL' ) ),
        );
    }

    /**
     * @return Content
     */
    private function generateContent()
    {
        return new Content(
            array(
                'versionInfo' => new VersionInfo(
                    array(
                        'names' => $this->translatedNames,
                        'initialLanguageCode' => 'fre-FR'
                    )
                ),
                'internalFields' => $this->translatedFields
            )
        );
    }

    /**
     * @dataProvider getTranslatedNameProvider
     *
     * @param array $prioritizedLanguages
     * @param string $expectedLocale
     */
    public function testGetTranslatedName( array $prioritizedLanguages, $expectedLocale )
    {
        $content = $this->generateContent();
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'languages' )
            ->will( $this->returnValue( $prioritizedLanguages ) );

        $this->assertSame( $this->translatedNames[$expectedLocale], $this->contentHelper->getTranslatedContentName( $content ) );
    }

    /**
     * @dataProvider getTranslatedNameProvider
     *
     * @param array $prioritizedLanguages
     * @param string $expectedLocale
     */
    public function testGetTranslatedNameByContentInfo( array $prioritizedLanguages, $expectedLocale )
    {
        $content = $this->generateContent();
        $contentInfo = new ContentInfo( array( 'id' => 123 ) );
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'languages' )
            ->will( $this->returnValue( $prioritizedLanguages ) );

        $this->contentService
            ->expects( $this->once() )
            ->method( 'loadContentByContentInfo' )
            ->with( $contentInfo )
            ->will( $this->returnValue( $content ) );

        $this->assertSame( $this->translatedNames[$expectedLocale], $this->contentHelper->getTranslatedContentNameByContentInfo( $contentInfo ) );
    }

    public function getTranslatedNameProvider()
    {
        return array(
            array( array( 'fre-FR', 'eng-GB' ), 'fre-FR' ),
            array( array( 'esl-ES', 'fre-FR' ), 'esl-ES' ),
            array( array( 'eng-US', 'heb-IL' ), 'heb-IL' ),
            array( array( 'eng-US', 'eng-GB' ), 'eng-GB' ),
            array( array( 'eng-US', 'ger-DE' ), 'fre-FR' ),
        );
    }

    public function testGetTranslatedNameByContentInfoForcedLanguage()
    {
        $content = $this->generateContent();
        $contentInfo = new ContentInfo( array( 'id' => 123 ) );
        $this->configResolver
            ->expects( $this->never() )
            ->method( 'getParameter' );

        $this->contentService
            ->expects( $this->exactly( 2 ) )
            ->method( 'loadContentByContentInfo' )
            ->with( $contentInfo )
            ->will( $this->returnValue( $content ) );

        $this->assertSame( 'My name in english', $this->contentHelper->getTranslatedContentNameByContentInfo( $contentInfo, 'eng-GB' ) );
        $this->assertSame( 'Mon nom en français', $this->contentHelper->getTranslatedContentNameByContentInfo( $contentInfo, 'eng-US' ) );
    }

    public function testGetTranslatedNameByContentInfoForcedLanguageMainLanguage()
    {
        $name = 'Name in main language';
        $mainLanguage = 'eng-GB';
        $contentInfo = new ContentInfo(
            array(
                'id' => 123,
                'mainLanguageCode' => $mainLanguage,
                'name' => $name
            )
        );
        $this->configResolver
            ->expects( $this->never() )
            ->method( 'getParameter' );

        $this->contentService
            ->expects( $this->never() )
            ->method( 'loadContentByContentInfo' );

        $this->assertSame(
            $name,
            $this->contentHelper->getTranslatedContentNameByContentInfo( $contentInfo, $mainLanguage )
        );
    }

    public function testGetTranslatedNameForcedLanguage()
    {
        $content = $this->generateContent();
        $this->configResolver
            ->expects( $this->never() )
            ->method( 'getParameter' );

        $this->assertSame( 'My name in english', $this->contentHelper->getTranslatedContentName( $content, 'eng-GB' ) );
        $this->assertSame( 'Mon nom en français', $this->contentHelper->getTranslatedContentName( $content, 'eng-US' ) );
    }

    /**
     * @dataProvider getTranslatedFieldProvider
     *
     * @param array $prioritizedLanguages
     * @param string $expectedLocale
     */
    public function getTranslatedField( array $prioritizedLanguages, $expectedLocale )
    {
        $content = $this->generateContent();
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'languages' )
            ->will( $this->returnValue( $prioritizedLanguages ) );

        $this->assertSame( $this->translatedFields[$expectedLocale], $this->contentHelper->getTranslatedField( $content, 'test' ) );
    }

    public function getTranslatedFieldProvider()
    {
        return array(
            array( array( 'fre-FR', 'eng-GB' ), 'fre-FR' ),
            array( array( 'esl-ES', 'fre-FR' ), 'esl-ES' ),
            array( array( 'eng-US', 'heb-IL' ), 'heb-IL' ),
            array( array( 'eng-US', 'eng-GB' ), 'eng-GB' ),
            array( array( 'eng-US', 'ger-DE' ), 'fre-FR' ),
        );
    }
}
