<?php

/**
 * File containing the ContentHelper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Helper\Tests;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use PHPUnit\Framework\TestCase;
use eZ\Publish\Core\Helper\TranslationHelper;
use Psr\Log\LoggerInterface;

class TranslationHelperTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var \eZ\Publish\Core\Helper\TranslationHelper */
    private $translationHelper;

    private $siteAccessByLanguages;

    /** @var Field[] */
    private $translatedFields;

    /** @var string[] */
    private $translatedNames;

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->contentService = $this->createMock(ContentService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->siteAccessByLanguages = [
            'fre-FR' => ['fre'],
            'eng-GB' => ['my_siteaccess', 'eng'],
            'esl-ES' => ['esl', 'mex'],
            'heb-IL' => ['heb'],
        ];
        $this->translationHelper = new TranslationHelper(
            $this->configResolver,
            $this->contentService,
            $this->siteAccessByLanguages,
            $this->logger
        );
        $this->translatedNames = [
            'eng-GB' => 'My name in english',
            'fre-FR' => 'Mon nom en français',
            'esl-ES' => 'Mi nombre en español',
            'heb-IL' => 'השם שלי בעברית',
        ];
        $this->translatedFields = [
            'eng-GB' => new Field(['value' => 'Content in english', 'fieldDefIdentifier' => 'test', 'languageCode' => 'eng-GB']),
            'fre-FR' => new Field(['value' => 'Contenu en français', 'fieldDefIdentifier' => 'test', 'languageCode' => 'fre-FR']),
            'esl-ES' => new Field(['value' => 'Contenido en español', 'fieldDefIdentifier' => 'test', 'languageCode' => 'esl-ES']),
            'heb-IL' => new Field(['value' => 'תוכן בספרדית', 'fieldDefIdentifier' => 'test', 'languageCode' => 'heb-IL']),
        ];
    }

    /**
     * @return Content
     */
    private function generateContent()
    {
        return new Content(
            [
                'versionInfo' => $this->generateVersionInfo(),
                'internalFields' => $this->translatedFields,
            ]
        );
    }

    /**
     * @return APIVersionInfo
     */
    private function generateVersionInfo()
    {
        return new VersionInfo(
            [
                'names' => $this->translatedNames,
                'initialLanguageCode' => 'fre-FR',
            ]
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
        $versionInfo = $this->generateVersionInfo();
        $contentInfo = new ContentInfo(['id' => 123]);
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('languages')
            ->will($this->returnValue($prioritizedLanguages));

        $this->contentService
            ->expects($this->once())
            ->method('loadVersionInfo')
            ->with($contentInfo)
            ->will($this->returnValue($versionInfo));

        $this->assertSame($this->translatedNames[$expectedLocale], $this->translationHelper->getTranslatedContentNameByContentInfo($contentInfo));
    }

    public function getTranslatedNameProvider()
    {
        return [
            [['fre-FR', 'eng-GB'], 'fre-FR'],
            [['esl-ES', 'fre-FR'], 'esl-ES'],
            [['eng-US', 'heb-IL'], 'heb-IL'],
            [['eng-US', 'eng-GB'], 'eng-GB'],
            [['eng-US', 'ger-DE'], 'fre-FR'],
        ];
    }

    public function testGetTranslatedNameByContentInfoForcedLanguage()
    {
        $versionInfo = $this->generateVersionInfo();
        $contentInfo = new ContentInfo(['id' => 123]);
        $this->configResolver
            ->expects($this->never())
            ->method('getParameter');

        $this->contentService
            ->expects($this->exactly(2))
            ->method('loadVersionInfo')
            ->with($contentInfo)
            ->will($this->returnValue($versionInfo));

        $this->assertSame('My name in english', $this->translationHelper->getTranslatedContentNameByContentInfo($contentInfo, 'eng-GB'));
        $this->assertSame('Mon nom en français', $this->translationHelper->getTranslatedContentNameByContentInfo($contentInfo, 'eng-US'));
    }

    public function testGetTranslatedNameByContentInfoForcedLanguageMainLanguage()
    {
        $name = 'Name in main language';
        $mainLanguage = 'eng-GB';
        $contentInfo = new ContentInfo(
            [
                'id' => 123,
                'mainLanguageCode' => $mainLanguage,
                'name' => $name,
            ]
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
        return [
            [['fre-FR', 'eng-GB'], 'fre-FR'],
            [['esl-ES', 'fre-FR'], 'esl-ES'],
            [['eng-US', 'heb-IL'], 'heb-IL'],
            [['eng-US', 'eng-GB'], 'eng-GB'],
            [['eng-US', 'ger-DE'], 'fre-FR'],
        ];
    }

    public function testGetTranslationSiteAccessUnkownLanguage()
    {
        $this->configResolver
            ->expects($this->exactly(2))
            ->method('getParameter')
            ->will(
                $this->returnValueMap(
                    [
                        ['translation_siteaccesses', null, null, []],
                        ['related_siteaccesses', null, null, []],
                    ]
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
                    [
                        ['translation_siteaccesses', null, null, $translationSiteAccesses],
                        ['related_siteaccesses', null, null, $relatedSiteAccesses],
                    ]
                )
            );

        $this->assertSame($expectedResult, $this->translationHelper->getTranslationSiteAccess($language));
    }

    public function getTranslationSiteAccessProvider()
    {
        return [
            ['eng-GB', ['fre', 'eng', 'heb'], ['esl', 'fre', 'eng', 'heb'], 'eng'],
            ['eng-GB', [], ['esl', 'fre', 'eng', 'heb'], 'eng'],
            ['eng-GB', [], ['esl', 'fre', 'eng', 'heb', 'my_siteaccess'], 'my_siteaccess'],
            ['eng-GB', ['esl', 'fre', 'eng', 'heb', 'my_siteaccess'], [], 'my_siteaccess'],
            ['eng-GB', ['esl', 'fre', 'eng', 'heb'], [], 'eng'],
            ['fre-FR', ['esl', 'fre', 'eng', 'heb'], [], 'fre'],
            ['fre-FR', ['esl', 'eng', 'heb'], [], null],
            ['heb-IL', ['esl', 'eng'], [], null],
            ['esl-ES', [], ['esl', 'mex', 'fre', 'eng', 'heb'], 'esl'],
            ['esl-ES', [], ['mex', 'fre', 'eng', 'heb'], 'mex'],
            ['esl-ES', ['esl', 'mex', 'fre', 'eng', 'heb'], [], 'esl'],
            ['esl-ES', ['mex', 'fre', 'eng', 'heb'], [], 'mex'],
        ];
    }

    public function testGetAvailableLanguagesWithTranslationSiteAccesses()
    {
        $this->configResolver
            ->expects($this->any())
            ->method('getParameter')
            ->will(
                $this->returnValueMap(
                    [
                        ['translation_siteaccesses', null, null, ['fre', 'esl']],
                        ['related_siteaccesses', null, null, ['fre', 'esl', 'heb']],
                        ['languages', null, null, ['eng-GB']],
                        ['languages', null, 'fre', ['fre-FR', 'eng-GB']],
                        ['languages', null, 'esl', ['esl-ES', 'fre-FR', 'eng-GB']],
                    ]
                )
            );

        $expectedLanguages = ['eng-GB', 'esl-ES', 'fre-FR'];
        $this->assertSame($expectedLanguages, $this->translationHelper->getAvailableLanguages());
    }

    public function testGetAvailableLanguagesWithoutTranslationSiteAccesses()
    {
        $this->configResolver
            ->expects($this->any())
            ->method('getParameter')
            ->will(
                $this->returnValueMap(
                    [
                        ['translation_siteaccesses', null, null, []],
                        ['related_siteaccesses', null, null, ['fre', 'esl', 'heb']],
                        ['languages', null, null, ['eng-GB']],
                        ['languages', null, 'fre', ['fre-FR', 'eng-GB']],
                        ['languages', null, 'esl', ['esl-ES', 'fre-FR', 'eng-GB']],
                        ['languages', null, 'heb', ['heb-IL', 'eng-GB']],
                    ]
                )
            );

        $expectedLanguages = ['eng-GB', 'esl-ES', 'fre-FR', 'heb-IL'];
        $this->assertSame($expectedLanguages, $this->translationHelper->getAvailableLanguages());
    }
}
