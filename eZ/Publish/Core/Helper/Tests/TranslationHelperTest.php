<?php

/**
 * File containing the ContentHelper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Helper\Tests;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    private $translationHelper;

    private $siteAccessByLanguages;

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
        $this->configResolver = $this->getMock('eZ\\Publish\\Core\\MVC\\ConfigResolverInterface');
        $this->contentService = $this->getMock('eZ\\Publish\\API\\Repository\\ContentService');
        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->siteAccessByLanguages = array(
            'fre-FR' => array('fre'),
            'eng-GB' => array('my_siteaccess', 'eng'),
            'esl-ES' => array('esl', 'mex'),
            'heb-IL' => array('heb'),
        );
        $this->translationHelper = new TranslationHelper(
            $this->configResolver,
            $this->contentService,
            $this->siteAccessByLanguages,
            $this->logger
        );
        $this->translatedNames = array(
            'eng-GB' => 'My name in english',
            'fre-FR' => 'Mon nom en français',
            'esl-ES' => 'Mi nombre en español',
            'heb-IL' => 'השם שלי בעברית',
        );
        $this->translatedFields = array(
            'eng-GB' => new Field(array('value' => 'Content in english', 'fieldDefIdentifier' => 'test', 'languageCode' => 'eng-GB')),
            'fre-FR' => new Field(array('value' => 'Contenu en français', 'fieldDefIdentifier' => 'test', 'languageCode' => 'fre-FR')),
            'esl-ES' => new Field(array('value' => 'Contenido en español', 'fieldDefIdentifier' => 'test', 'languageCode' => 'esl-ES')),
            'heb-IL' => new Field(array('value' => 'תוכן בספרדית', 'fieldDefIdentifier' => 'test', 'languageCode' => 'heb-IL')),
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
                        'initialLanguageCode' => 'fre-FR',
                    )
                ),
                'internalFields' => $this->translatedFields,
            )
        );
    }

    /**
     * @dataProvider getTranslatedNameProvider
     *
     * @param array $prioritizedLanguages
     * @param string $expectedLocale
     */
    public function testGetTranslatedName(array $prioritizedLanguages, $expectedLocale)
    {
        $content = $this->generateContent();
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('languages')
            ->will($this->returnValue($prioritizedLanguages));

        $this->assertSame($this->translatedNames[$expectedLocale], $this->translationHelper->getTranslatedContentName($content));
    }

    /**
     * @dataProvider getTranslatedNameProvider
     *
     * @param array $prioritizedLanguages
     * @param string $expectedLocale
     */
    public function testGetTranslatedNameByContentInfo(array $prioritizedLanguages, $expectedLocale)
    {
        $content = $this->generateContent();
        $contentInfo = new ContentInfo(array('id' => 123));
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('languages')
            ->will($this->returnValue($prioritizedLanguages));

        $this->contentService
            ->expects($this->once())
            ->method('loadContentByContentInfo')
            ->with($contentInfo)
            ->will($this->returnValue($content));

        $this->assertSame($this->translatedNames[$expectedLocale], $this->translationHelper->getTranslatedContentNameByContentInfo($contentInfo));
    }

    public function getTranslatedNameProvider()
    {
        return array(
            array(array('fre-FR', 'eng-GB'), 'fre-FR'),
            array(array('esl-ES', 'fre-FR'), 'esl-ES'),
            array(array('eng-US', 'heb-IL'), 'heb-IL'),
            array(array('eng-US', 'eng-GB'), 'eng-GB'),
            array(array('eng-US', 'ger-DE'), 'fre-FR'),
        );
    }

    public function testGetTranslatedNameByContentInfoForcedLanguage()
    {
        $content = $this->generateContent();
        $contentInfo = new ContentInfo(array('id' => 123));
        $this->configResolver
            ->expects($this->never())
            ->method('getParameter');

        $this->contentService
            ->expects($this->exactly(2))
            ->method('loadContentByContentInfo')
            ->with($contentInfo)
            ->will($this->returnValue($content));

        $this->assertSame('My name in english', $this->translationHelper->getTranslatedContentNameByContentInfo($contentInfo, 'eng-GB'));
        $this->assertSame('Mon nom en français', $this->translationHelper->getTranslatedContentNameByContentInfo($contentInfo, 'eng-US'));
    }

    public function testGetTranslatedNameByContentInfoForcedLanguageMainLanguage()
    {
        $name = 'Name in main language';
        $mainLanguage = 'eng-GB';
        $contentInfo = new ContentInfo(
            array(
                'id' => 123,
                'mainLanguageCode' => $mainLanguage,
                'name' => $name,
            )
        );
        $this->configResolver
            ->expects($this->never())
            ->method('getParameter');

        $this->contentService
            ->expects($this->never())
            ->method('loadContentByContentInfo');

        $this->assertSame(
            $name,
            $this->translationHelper->getTranslatedContentNameByContentInfo($contentInfo, $mainLanguage)
        );
    }

    public function testGetTranslatedNameForcedLanguage()
    {
        $content = $this->generateContent();
        $this->configResolver
            ->expects($this->never())
            ->method('getParameter');

        $this->assertSame('My name in english', $this->translationHelper->getTranslatedContentName($content, 'eng-GB'));
        $this->assertSame('Mon nom en français', $this->translationHelper->getTranslatedContentName($content, 'eng-US'));
    }

    /**
     * @dataProvider getTranslatedFieldProvider
     *
     * @param array $prioritizedLanguages
     * @param string $expectedLocale
     */
    public function getTranslatedField(array $prioritizedLanguages, $expectedLocale)
    {
        $content = $this->generateContent();
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('languages')
            ->will($this->returnValue($prioritizedLanguages));

        $this->assertSame($this->translatedFields[$expectedLocale], $this->translationHelper->getTranslatedField($content, 'test'));
    }

    public function getTranslatedFieldProvider()
    {
        return array(
            array(array('fre-FR', 'eng-GB'), 'fre-FR'),
            array(array('esl-ES', 'fre-FR'), 'esl-ES'),
            array(array('eng-US', 'heb-IL'), 'heb-IL'),
            array(array('eng-US', 'eng-GB'), 'eng-GB'),
            array(array('eng-US', 'ger-DE'), 'fre-FR'),
        );
    }

    public function testGetTranslationSiteAccessUnkownLanguage()
    {
        $this->configResolver
            ->expects($this->exactly(2))
            ->method('getParameter')
            ->will(
                $this->returnValueMap(
                    array(
                        array('translation_siteaccesses', null, null, array()),
                        array('related_siteaccesses', null, null, array()),
                    )
                )
            );

        $this->logger
            ->expects($this->once())
            ->method('error');

        $this->assertNull($this->translationHelper->getTranslationSiteAccess('eng-DE'));
    }

    /**
     * @dataProvider getTranslationSiteAccessProvider
     */
    public function testGetTranslationSiteAccess($language, array $translationSiteAccesses, array $relatedSiteAccesses, $expectedResult)
    {
        $this->configResolver
            ->expects($this->exactly(2))
            ->method('getParameter')
            ->will(
                $this->returnValueMap(
                    array(
                        array('translation_siteaccesses', null, null, $translationSiteAccesses),
                        array('related_siteaccesses', null, null, $relatedSiteAccesses),
                    )
                )
            );

        $this->assertSame($expectedResult, $this->translationHelper->getTranslationSiteAccess($language));
    }

    public function getTranslationSiteAccessProvider()
    {
        return array(
            array('eng-GB', array('fre', 'eng', 'heb'), array('esl', 'fre', 'eng', 'heb'), 'eng'),
            array('eng-GB', array(), array('esl', 'fre', 'eng', 'heb'), 'eng'),
            array('eng-GB', array(), array('esl', 'fre', 'eng', 'heb', 'my_siteaccess'), 'my_siteaccess'),
            array('eng-GB', array('esl', 'fre', 'eng', 'heb', 'my_siteaccess'), array(), 'my_siteaccess'),
            array('eng-GB', array('esl', 'fre', 'eng', 'heb'), array(), 'eng'),
            array('fre-FR', array('esl', 'fre', 'eng', 'heb'), array(), 'fre'),
            array('fre-FR', array('esl', 'eng', 'heb'), array(), null),
            array('heb-IL', array('esl', 'eng'), array(), null),
            array('esl-ES', array(), array('esl', 'mex', 'fre', 'eng', 'heb'), 'esl'),
            array('esl-ES', array(), array('mex', 'fre', 'eng', 'heb'), 'mex'),
            array('esl-ES', array('esl', 'mex', 'fre', 'eng', 'heb'), array(), 'esl'),
            array('esl-ES', array('mex', 'fre', 'eng', 'heb'), array(), 'mex'),
        );
    }

    public function testGetAvailableLanguagesWithTranslationSiteAccesses()
    {
        $this->configResolver
            ->expects($this->any())
            ->method('getParameter')
            ->will(
                $this->returnValueMap(
                    array(
                        array('translation_siteaccesses', null, null, array('fre', 'esl')),
                        array('related_siteaccesses', null, null, array('fre', 'esl', 'heb')),
                        array('languages', null, null, array('eng-GB')),
                        array('languages', null, 'fre', array('fre-FR', 'eng-GB')),
                        array('languages', null, 'esl', array('esl-ES', 'fre-FR', 'eng-GB')),
                    )
                )
            );

        $expectedLanguages = array('eng-GB', 'esl-ES', 'fre-FR');
        $this->assertSame($expectedLanguages, $this->translationHelper->getAvailableLanguages());
    }

    public function testGetAvailableLanguagesWithoutTranslationSiteAccesses()
    {
        $this->configResolver
            ->expects($this->any())
            ->method('getParameter')
            ->will(
                $this->returnValueMap(
                    array(
                        array('translation_siteaccesses', null, null, array()),
                        array('related_siteaccesses', null, null, array('fre', 'esl', 'heb')),
                        array('languages', null, null, array('eng-GB')),
                        array('languages', null, 'fre', array('fre-FR', 'eng-GB')),
                        array('languages', null, 'esl', array('esl-ES', 'fre-FR', 'eng-GB')),
                        array('languages', null, 'heb', array('heb-IL', 'eng-GB')),
                    )
                )
            );

        $expectedLanguages = array('eng-GB', 'esl-ES', 'fre-FR', 'heb-IL');
        $this->assertSame($expectedLanguages, $this->translationHelper->getAvailableLanguages());
    }
}
