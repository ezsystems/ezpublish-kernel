<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;

class DomainMapperTest extends SiteAccessAwareRepositoryTest
{
    public function getData()
    {
        return [
            [
                1,
                1,
                'eng-GB',
                [
                    'eng-GB',
                ],
                [
                    'eng-GB' => 'First',
                ],
                [
                    'eng-GB' => [
                        'first' => 'First field',
                    ],
                ],
            ],
            [
                2,
                1,
                'eng-GB',
                [
                    'eng-GB',
                    'fre-FR',
                ],
                [
                    'eng-GB' => 'First',
                    'fre-FR' => 'Premier',
                ],
                [
                    'eng-GB' => [
                        'first' => 'First field',
                    ],
                    'fre-FR' => [
                        'first' => 'Premier champ',
                    ]
                ],
            ],
            [
                3,
                1,
                'fre-FR',
                [
                    'eng-GB',
                    'fre-FR',
                ],
                [
                    'eng-GB' => 'First',
                    'fre-FR' => 'Premier',
                ],
                [
                    'eng-GB' => [
                        'first' => 'First field',
                    ],
                    'fre-FR' => [
                        'first' => 'Premier champ',
                    ]
                ],
            ],
            [
                4,
                1,
                'ger-DE',
                [
                    'eng-GB',
                    'ger-DE',
                ],
                [
                    'eng-GB' => 'First',
                    'ger-DE' => 'Erste',
                ],
                [
                    'eng-GB' => [
                        'first' => 'First field',
                    ],
                    'ger-DE' => [
                        'first' => 'Erstes feld',
                    ]
                ],
            ],
            [
                5,
                1,
                'ger-DE',
                [
                    'fre-FR',
                    'ger-DE',
                ],
                [
                    'fre-FR' => 'Premier',
                    'ger-DE' => 'Erste',
                ],
                [
                    'fre-FR' => [
                        'first' => 'Premier champ',
                    ],
                    'ger-DE' => [
                        'first' => 'Erstes feld',
                    ]
                ],
            ],
            [
                6,
                1,
                'ger-DE',
                [
                    'ger-DE',
                    'fre-FR',
                ],
                [
                    'fre-FR' => 'Premier',
                    'ger-DE' => 'Erste',
                ],
                [
                    'fre-FR' => [
                        'first' => 'Premier champ',
                    ],
                    'ger-DE' => [
                        'first' => 'Erstes feld',
                    ]
                ],
            ],
        ];
    }

    /**
     * @dataProvider getData
     *
     * @param $contentId
     * @param $versionNo
     * @param $mainLanguageCode
     * @param array $languageCodes
     * @param array $names
     * @param array $fields
     */
    public function testRebuildContentInfoBasicMatchFirst($contentId, $versionNo, $mainLanguageCode, array $languageCodes, array $names, array $fields)
    {
        $apiContentInfo = new ContentInfo([
            'id' => $contentId,
            'mainLanguageCode' => $mainLanguageCode,
            'currentVersionNo' => $versionNo,
            'name' => $names[$mainLanguageCode],
        ]);

        $reverseLanguages = array_reverse($languageCodes);

        $contentInfo = $this->domainMapper->rebuildContentInfoDomainObject($apiContentInfo, $reverseLanguages);

        $this->assertEquals($reverseLanguages[0], $contentInfo->languageCode);
        $this->assertEquals($names[$reverseLanguages[0]], $contentInfo->name);
    }

    /**
     * @dataProvider getData
     *
     * @param $contentId
     * @param $versionNo
     * @param $mainLanguageCode
     * @param array $languageCodes
     * @param array $names
     * @param array $fields
     */
    public function testRebuildContentInfoFallbackToMain($contentId, $versionNo, $mainLanguageCode, array $languageCodes, array $names, array $fields)
    {
        $apiContentInfo = new ContentInfo([
            'id' => $contentId,
            'mainLanguageCode' => $mainLanguageCode,
            'currentVersionNo' => $versionNo,
            'name' => $names[$mainLanguageCode],
        ]);

        $contentInfo = $this->domainMapper->rebuildContentInfoDomainObject($apiContentInfo, ['pol-PL']);

        $this->assertEquals($mainLanguageCode, $contentInfo->languageCode);
        $this->assertEquals($names[$mainLanguageCode], $contentInfo->name);
    }

    /**
     * @dataProvider getData
     *
     * @param $contentId
     * @param $versionNo
     * @param $mainLanguageCode
     * @param array $languageCodes
     * @param array $names
     * @param array $fields
     */
    public function testRebuildContentInfoPickFirstFoundMatch($contentId, $versionNo, $mainLanguageCode, array $languageCodes, array $names, array $fields)
    {
        $apiContentInfo = new ContentInfo([
            'id' => $contentId,
            'mainLanguageCode' => $mainLanguageCode,
            'currentVersionNo' => $versionNo,
            'name' => $names[$mainLanguageCode],
        ]);

        $reverseLanguages = array_reverse($languageCodes);
        $languages = array_merge(['pol-PL', 'rus-RU'], $reverseLanguages);

        $contentInfo = $this->domainMapper->rebuildContentInfoDomainObject($apiContentInfo, $languages);

        $this->assertEquals($reverseLanguages[0], $contentInfo->languageCode);
        $this->assertEquals($names[$reverseLanguages[0]], $contentInfo->name);
    }

    /**
     * @dataProvider getData
     *
     * @param $contentId
     * @param $versionNo
     * @param $mainLanguageCode
     * @param array $languageCodes
     * @param array $names
     * @param array $fields
     */
    public function testRebuildVersionInfoBasicMatchFirst($contentId, $versionNo, $mainLanguageCode, array $languageCodes, array $names, array $fields)
    {
        $apiContentInfo = new ContentInfo([
            'id' => $contentId,
            'mainLanguageCode' => $mainLanguageCode,
            'currentVersionNo' => $versionNo,
            'name' => $names[$mainLanguageCode],
        ]);

        $apiVersionInfo = new VersionInfo([
            'names' => $names,
            'versionNo' => $versionNo,
            'initialLanguageCode' => $mainLanguageCode,
            'languageCodes' => $languageCodes,
            'contentInfo' => $apiContentInfo,
        ]);

        $reverseLanguages = array_reverse($languageCodes);

        $versionInfo = $this->domainMapper->rebuildVersionInfoDomainObject($apiVersionInfo, $reverseLanguages);

        $this->assertEquals($reverseLanguages[0], $versionInfo->languageCode);
        $this->assertEquals($names[$reverseLanguages[0]], $versionInfo->name);
        $this->assertEquals($reverseLanguages[0], $versionInfo->getContentInfo()->languageCode);
        $this->assertEquals($names[$reverseLanguages[0]], $versionInfo->getContentInfo()->name);
    }

    /**
     * @dataProvider getData
     *
     * @param $contentId
     * @param $versionNo
     * @param $mainLanguageCode
     * @param array $languageCodes
     * @param array $names
     * @param array $fields
     */
    public function testRebuildVersionInfoFallbackToMain($contentId, $versionNo, $mainLanguageCode, array $languageCodes, array $names, array $fields)
    {
        $apiContentInfo = new ContentInfo([
            'id' => $contentId,
            'mainLanguageCode' => $mainLanguageCode,
            'currentVersionNo' => $versionNo,
            'name' => $names[$mainLanguageCode],
        ]);

        $apiVersionInfo = new VersionInfo([
            'names' => $names,
            'versionNo' => $versionNo,
            'initialLanguageCode' => $mainLanguageCode,
            'languageCodes' => $languageCodes,
            'contentInfo' => $apiContentInfo,
        ]);

        $versionInfo = $this->domainMapper->rebuildVersionInfoDomainObject($apiVersionInfo, ['pol-PL']);

        $this->assertEquals($mainLanguageCode, $versionInfo->languageCode);
        $this->assertEquals($names[$mainLanguageCode], $versionInfo->name);
        $this->assertEquals($mainLanguageCode, $versionInfo->getContentInfo()->languageCode);
        $this->assertEquals($names[$mainLanguageCode], $versionInfo->getContentInfo()->name);
    }

    /**
     * @dataProvider getData
     *
     * @param $contentId
     * @param $versionNo
     * @param $mainLanguageCode
     * @param array $languageCodes
     * @param array $names
     * @param array $fields
     */
    public function testRebuildVersionInfoPickFirstFoundMatch($contentId, $versionNo, $mainLanguageCode, array $languageCodes, array $names, array $fields)
    {
        $apiContentInfo = new ContentInfo([
            'id' => $contentId,
            'mainLanguageCode' => $mainLanguageCode,
            'currentVersionNo' => $versionNo,
            'name' => $names[$mainLanguageCode],
        ]);

        $apiVersionInfo = new VersionInfo([
            'names' => $names,
            'versionNo' => $versionNo,
            'initialLanguageCode' => $mainLanguageCode,
            'languageCodes' => $languageCodes,
            'contentInfo' => $apiContentInfo,
        ]);

        $reverseLanguages = array_reverse($languageCodes);
        $languages = array_merge(['pol-PL', 'rus-RU'], $reverseLanguages);

        $versionInfo = $this->domainMapper->rebuildVersionInfoDomainObject($apiVersionInfo, $languages);

        $this->assertEquals($reverseLanguages[0], $versionInfo->languageCode);
        $this->assertEquals($names[$reverseLanguages[0]], $versionInfo->name);
        $this->assertEquals($reverseLanguages[0], $versionInfo->getContentInfo()->languageCode);
        $this->assertEquals($names[$reverseLanguages[0]], $versionInfo->getContentInfo()->name);
    }

    /**
     * @dataProvider getData
     *
     * @param $contentId
     * @param $versionNo
     * @param $mainLanguageCode
     * @param array $languageCodes
     * @param array $names
     * @param array $fields
     */
    public function testRebuildContentBasicMatchFirst($contentId, $versionNo, $mainLanguageCode, array $languageCodes, array $names, array $fields)
    {
        $apiContentInfo = new ContentInfo([
            'id' => $contentId,
            'mainLanguageCode' => $mainLanguageCode,
            'currentVersionNo' => $versionNo,
            'name' => $names[$mainLanguageCode],
        ]);

        $apiVersionInfo = new VersionInfo([
            'names' => $names,
            'versionNo' => $versionNo,
            'initialLanguageCode' => $mainLanguageCode,
            'languageCodes' => $languageCodes,
            'contentInfo' => $apiContentInfo,
        ]);

        $apiFields = [];

        foreach ($fields as $languageCode => $translatedFields) {
            foreach ($translatedFields as $identifier => $value) {
                $apiFields[] = new Field([
                    'fieldDefIdentifier' => $identifier,
                    'value' => $value,
                    'languageCode' => $languageCode,
                ]);
            }
        }

        $apiContent = new Content([
            'versionInfo' => $apiVersionInfo,
            'internalFields' => $apiFields,
        ]);

        $reverseLanguages = array_reverse($languageCodes);

        $content = $this->domainMapper->rebuildContentDomainObject($apiContent, $reverseLanguages);

        $this->assertEquals($reverseLanguages[0], $content->versionInfo->languageCode);
        $this->assertEquals($names[$reverseLanguages[0]], $content->versionInfo->name);
        $this->assertEquals($reverseLanguages[0], $content->versionInfo->getContentInfo()->languageCode);
        $this->assertEquals($names[$reverseLanguages[0]], $content->versionInfo->getContentInfo()->name);
        $this->assertEquals($fields[$reverseLanguages[0]]['first'], $content->getField('first')->value);
        $this->assertEquals($reverseLanguages[0], $content->languageCode);
    }

    /**
     * @dataProvider getData
     *
     * @param $contentId
     * @param $versionNo
     * @param $mainLanguageCode
     * @param array $languageCodes
     * @param array $names
     * @param array $fields
     */
    public function testRebuildContentFallbackToMain($contentId, $versionNo, $mainLanguageCode, array $languageCodes, array $names, array $fields)
    {
        $apiContentInfo = new ContentInfo([
            'id' => $contentId,
            'mainLanguageCode' => $mainLanguageCode,
            'currentVersionNo' => $versionNo,
            'name' => $names[$mainLanguageCode],
        ]);

        $apiVersionInfo = new VersionInfo([
            'names' => $names,
            'versionNo' => $versionNo,
            'initialLanguageCode' => $mainLanguageCode,
            'languageCodes' => $languageCodes,
            'contentInfo' => $apiContentInfo,
        ]);

        $apiFields = [];

        foreach ($fields as $languageCode => $translatedFields) {
            foreach ($translatedFields as $identifier => $value) {
                $apiFields[] = new Field([
                    'fieldDefIdentifier' => $identifier,
                    'value' => $value,
                    'languageCode' => $languageCode,
                ]);
            }
        }

        $apiContent = new Content([
            'versionInfo' => $apiVersionInfo,
            'internalFields' => $apiFields,
        ]);

        $content = $this->domainMapper->rebuildContentDomainObject($apiContent, ['pol-PL']);

        $this->assertEquals($mainLanguageCode, $content->versionInfo->languageCode);
        $this->assertEquals($names[$mainLanguageCode], $content->versionInfo->name);
        $this->assertEquals($mainLanguageCode, $content->versionInfo->getContentInfo()->languageCode);
        $this->assertEquals($names[$mainLanguageCode], $content->versionInfo->getContentInfo()->name);
        $this->assertEquals($fields[$mainLanguageCode]['first'], $content->getField('first')->value);
        $this->assertEquals($mainLanguageCode, $content->languageCode);
    }

    /**
     * @dataProvider getData
     *
     * @param $contentId
     * @param $versionNo
     * @param $mainLanguageCode
     * @param array $languageCodes
     * @param array $names
     * @param array $fields
     */
    public function testRebuildContentPickFirstFoundMatch($contentId, $versionNo, $mainLanguageCode, array $languageCodes, array $names, array $fields)
    {
        $apiContentInfo = new ContentInfo([
            'id' => $contentId,
            'mainLanguageCode' => $mainLanguageCode,
            'currentVersionNo' => $versionNo,
            'name' => $names[$mainLanguageCode],
        ]);

        $apiVersionInfo = new VersionInfo([
            'names' => $names,
            'versionNo' => $versionNo,
            'initialLanguageCode' => $mainLanguageCode,
            'languageCodes' => $languageCodes,
            'contentInfo' => $apiContentInfo,
        ]);

        $apiFields = [];

        foreach ($fields as $languageCode => $translatedFields) {
            foreach ($translatedFields as $identifier => $value) {
                $apiFields[] = new Field([
                    'fieldDefIdentifier' => $identifier,
                    'value' => $value,
                    'languageCode' => $languageCode,
                ]);
            }
        }

        $apiContent = new Content([
            'versionInfo' => $apiVersionInfo,
            'internalFields' => $apiFields,
        ]);

        $reverseLanguages = array_reverse($languageCodes);
        $languages = array_merge(['pol-PL', 'rus-RU'], $reverseLanguages);

        $content = $this->domainMapper->rebuildContentDomainObject($apiContent, $languages);

        $this->assertEquals($reverseLanguages[0], $content->versionInfo->languageCode);
        $this->assertEquals($names[$reverseLanguages[0]], $content->versionInfo->name);
        $this->assertEquals($reverseLanguages[0], $content->versionInfo->getContentInfo()->languageCode);
        $this->assertEquals($names[$reverseLanguages[0]], $content->versionInfo->getContentInfo()->name);
        $this->assertEquals($fields[$reverseLanguages[0]]['first'], $content->getField('first')->value);
        $this->assertEquals($reverseLanguages[0], $content->languageCode);
    }
}