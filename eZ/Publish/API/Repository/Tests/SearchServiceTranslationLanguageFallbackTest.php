<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Tests\SetupFactory\LegacyElasticsearch;
use EzSystems\EzPlatformSolrSearchEngine\Tests\SetupFactory\LegacySetupFactory as LegacySolrSetupFactory;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use DateTime;
use RuntimeException;

/**
 * Test case for field filtering operations in the SearchService.
 *
 * @see eZ\Publish\API\Repository\SearchService
 * @group integration
 * @group search
 * @group language_fallback
 */
class SearchServiceTranslationLanguageFallbackTest extends BaseTest
{
    const SETUP_DEDICATED = 'dedicated';
    const SETUP_SHARED = 'shared';
    const SETUP_SINGLE = 'single';

    public function setUp()
    {
        $setupFactory = $this->getSetupFactory();

        if ($setupFactory instanceof LegacyElasticsearch) {
            $this->markTestIncomplete('Not implemented for Elasticsearch Search Engine');
        }

        parent::setUp();
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function createTestContentType()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $createStruct = $contentTypeService->newContentTypeCreateStruct('test-type');
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->names = array('eng-GB' => 'Test type');
        $createStruct->creatorId = 14;
        $createStruct->creationDate = new DateTime();

        $fieldCreate = $contentTypeService->newFieldDefinitionCreateStruct('search_field', 'ezinteger');
        $fieldCreate->names = array('eng-GB' => 'Search field');
        $fieldCreate->fieldGroup = 'main';
        $fieldCreate->position = 1;
        $fieldCreate->isTranslatable = true;
        $fieldCreate->isSearchable = true;

        $createStruct->addFieldDefinition($fieldCreate);

        $fieldCreate = $contentTypeService->newFieldDefinitionCreateStruct('sort_field', 'ezinteger');
        $fieldCreate->names = array('eng-GB' => 'Sort field');
        $fieldCreate->fieldGroup = 'main';
        $fieldCreate->position = 2;
        $fieldCreate->isTranslatable = false;
        $fieldCreate->isSearchable = true;

        $createStruct->addFieldDefinition($fieldCreate);

        $contentGroup = $contentTypeService->loadContentTypeGroupByIdentifier('Content');
        $contentTypeDraft = $contentTypeService->createContentType($createStruct, array($contentGroup));
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        $contentType = $contentTypeService->loadContentType($contentTypeDraft->id);

        return $contentType;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param array $searchValuesMap
     * @param string $mainLanguageCode
     * @param bool $alwaysAvailable
     * @param int $sortValue
     * @param array $parentLocationIds
     *
     * @return mixed
     */
    protected function createContent(
        $contentType,
        array $searchValuesMap,
        $mainLanguageCode,
        $alwaysAvailable,
        $sortValue,
        array $parentLocationIds
    ) {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, $mainLanguageCode);
        $contentCreateStruct->alwaysAvailable = $alwaysAvailable;

        foreach ($searchValuesMap as $languageCode => $searchValue) {
            $contentCreateStruct->setField('search_field', $searchValue, $languageCode);
        }

        $contentCreateStruct->setField('sort_field', $sortValue, $mainLanguageCode);

        $data = array();
        $data['content'] = $contentService->publishVersion(
            $contentService->createContent($contentCreateStruct)->versionInfo
        );

        foreach ($parentLocationIds as $parentLocationId) {
            $locationCreateStruct = $locationService->newLocationCreateStruct($parentLocationId);
            $data['locations'][] = $locationService->createLocation(
                $data['content']->contentInfo,
                $locationCreateStruct
            );
        }

        return $data;
    }

    /**
     * @param array $parentLocationIds
     *
     * @return array
     */
    public function createTestContent(array $parentLocationIds)
    {
        $repository = $this->getRepository();
        $languageService = $repository->getContentLanguageService();

        $langCreateStruct = $languageService->newLanguageCreateStruct();
        $langCreateStruct->languageCode = 'por-PT';
        $langCreateStruct->name = 'Portuguese (portuguese)';
        $langCreateStruct->enabled = true;

        $languageService->createLanguage($langCreateStruct);

        $contentType = $this->createTestContentType();

        $context = array(
            $repository,
            array(
                1 => $this->createContent(
                    $contentType,
                    array(
                        'eng-GB' => 1,
                        'ger-DE' => 2,
                        'por-PT' => 3,
                    ),
                    'eng-GB',
                    false,
                    1,
                    $parentLocationIds
                ),
                2 => $this->createContent(
                    $contentType,
                    array(
                        //"eng-GB" => ,
                        'ger-DE' => 1,
                        'por-PT' => 2,
                    ),
                    'por-PT',
                    true,
                    2,
                    $parentLocationIds
                ),
                3 => $this->createContent(
                    $contentType,
                    array(
                        //"eng-GB" => ,
                        //"ger-DE" => ,
                        'por-PT' => 1,
                    ),
                    'por-PT',
                    false,
                    3,
                    $parentLocationIds
                ),
            ),
        );

        $this->refreshSearch($repository);

        return $context;
    }

    public function testCreateTestContent()
    {
        return $this->createTestContent(array(2, 12));
    }

    public function providerForTestFind()
    {
        return array(
            0 => array(
                array(
                    'languages' => array(
                        'eng-GB',
                        'ger-DE',
                        'por-PT',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    array(
                        1,
                        'eng-GB',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core0',
                            self::SETUP_SHARED => 'localhost:8983/solr/core3',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            1 => array(
                array(
                    'languages' => array(
                        'eng-GB',
                        'por-PT',
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    array(
                        1,
                        'eng-GB',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core0',
                            self::SETUP_SHARED => 'localhost:8983/solr/core3',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            2 => array(
                array(
                    'languages' => array(
                        'ger-DE',
                        'eng-GB',
                        'por-PT',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    array(
                        1,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            3 => array(
                array(
                    'languages' => array(
                        'ger-DE',
                        'por-PT',
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    array(
                        1,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            4 => array(
                array(
                    'languages' => array(
                        'por-PT',
                        'eng-GB',
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    array(
                        1,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            5 => array(
                array(
                    'languages' => array(
                        'por-PT',
                        'eng-GB',
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    array(
                        1,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            6 => array(
                array(
                    'languages' => array(
                        'eng-GB',
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    array(
                        1,
                        'eng-GB',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core0',
                            self::SETUP_SHARED => 'localhost:8983/solr/core3',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            7 => array(
                array(
                    'languages' => array(
                        'ger-DE',
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    array(
                        1,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            8 => array(
                array(
                    'languages' => array(
                        'eng-GB',
                        'por-PT',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    array(
                        1,
                        'eng-GB',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core0',
                            self::SETUP_SHARED => 'localhost:8983/solr/core3',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            9 => array(
                array(
                    'languages' => array(
                        'por-PT',
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    array(
                        1,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            10 => array(
                array(
                    'languages' => array(
                        'ger-DE',
                        'por-PT',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    array(
                        1,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            11 => array(
                array(
                    'languages' => array(
                        'por-PT',
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    array(
                        1,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            12 => array(
                array(
                    'languages' => array(
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    array(
                        1,
                        'eng-GB',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core0',
                            self::SETUP_SHARED => 'localhost:8983/solr/core3',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            13 => array(
                array(
                    'languages' => array(
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    array(
                        1,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            14 => array(
                array(
                    'languages' => array(
                        'por-PT',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    array(
                        1,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            15 => array(
                array(
                    'languages' => array(
                        'eng-US',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(),
            ),
            16 => array(
                array(
                    'languages' => array(
                        'eng-GB',
                        'ger-DE',
                        'por-PT',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    array(
                        1,
                        'eng-GB',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core0',
                            self::SETUP_SHARED => 'localhost:8983/solr/core3',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            17 => array(
                array(
                    'languages' => array(
                        'eng-GB',
                        'por-PT',
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    array(
                        1,
                        'eng-GB',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core0',
                            self::SETUP_SHARED => 'localhost:8983/solr/core3',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            18 => array(
                array(
                    'languages' => array(
                        'ger-DE',
                        'eng-GB',
                        'por-PT',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    array(
                        1,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            19 => array(
                array(
                    'languages' => array(
                        'ger-DE',
                        'por-PT',
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    array(
                        1,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            20 => array(
                array(
                    'languages' => array(
                        'por-PT',
                        'eng-GB',
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    array(
                        1,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            21 => array(
                array(
                    'languages' => array(
                        'por-PT',
                        'eng-GB',
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    array(
                        1,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            22 => array(
                array(
                    'languages' => array(
                        'eng-GB',
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    array(
                        1,
                        'eng-GB',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core0',
                            self::SETUP_SHARED => 'localhost:8983/solr/core3',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            23 => array(
                array(
                    'languages' => array(
                        'ger-DE',
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    array(
                        1,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            24 => array(
                array(
                    'languages' => array(
                        'eng-GB',
                        'por-PT',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    array(
                        1,
                        'eng-GB',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core0',
                            self::SETUP_SHARED => 'localhost:8983/solr/core3',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            25 => array(
                array(
                    'languages' => array(
                        'por-PT',
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    array(
                        1,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            26 => array(
                array(
                    'languages' => array(
                        'ger-DE',
                        'por-PT',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    array(
                        1,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            27 => array(
                array(
                    'languages' => array(
                        'por-PT',
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    array(
                        1,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            28 => array(
                array(
                    'languages' => array(
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    array(
                        1,
                        'eng-GB',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core0',
                            self::SETUP_SHARED => 'localhost:8983/solr/core3',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core0',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            29 => array(
                array(
                    'languages' => array(
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    array(
                        1,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'ger-DE',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core3',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            30 => array(
                array(
                    'languages' => array(
                        'por-PT',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    array(
                        1,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core2',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            31 => array(
                array(
                    'languages' => array(
                        'eng-US',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core0',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            32 => array(
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => true,
                ),
                $mainLanguages = array(
                    array(
                        1,
                        'eng-GB',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core0',
                            self::SETUP_SHARED => 'localhost:8983/solr/core0',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core0',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                    array(
                        3,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core0',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
            33 => array(
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => false,
                ),
                $mainLanguages,
            ),
            34 => array(
                array(
                    'useAlwaysAvailable' => true,
                ),
                $mainLanguages,
            ),
            35 => array(
                array(
                    'useAlwaysAvailable' => false,
                ),
                $mainLanguages,
            ),
            36 => array(
                array(
                    'languages' => array(),
                ),
                $mainLanguages,
            ),
            37 => array(
                array(),
                $mainLanguages,
            ),
            38 => array(
                array(
                    'languages' => array(
                        'eng-US',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(),
            ),
            39 => array(
                array(
                    'languages' => array(
                        'eng-US',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    array(
                        2,
                        'por-PT',
                        array(
                            self::SETUP_DEDICATED => 'localhost:8983/solr/core2',
                            self::SETUP_SHARED => 'localhost:8983/solr/core0',
                            self::SETUP_SINGLE => 'localhost:8983/solr/collection1',
                        ),
                    ),
                ),
            ),
        );
    }

    protected function getSetupType()
    {
        $coresSetup = getenv('CORES_SETUP');

        switch ($coresSetup) {
            case self::SETUP_DEDICATED:
                return self::SETUP_DEDICATED;
            case self::SETUP_SHARED:
                return self::SETUP_SHARED;
            case self::SETUP_SINGLE:
                return self::SETUP_SINGLE;
        }

        throw new RuntimeException("Backend cores setup '{$coresSetup}' is not handled");
    }

    protected function getIndexName($indexMap)
    {
        $setupFactory = $this->getSetupFactory();

        if ($setupFactory instanceof LegacySolrSetupFactory) {
            $setupType = $this->getSetupType();

            return $indexMap[$setupType];
        }

        return null;
    }

    /**
     * @dataProvider providerForTestFind
     * @depends testCreateTestContent
     *
     * @param array $languageSettings
     * @param array $contentDataList
     * @param array $context
     */
    public function testFindContent(
        array $languageSettings,
        array $contentDataList,
        array $context
    ) {
        /** @var \eZ\Publish\Api\Repository\Repository $repository */
        list($repository, $data) = $context;

        $queryProperties = array(
            'filter' => new Criterion\ContentTypeIdentifier('test-type'),
            'sortClauses' => array(
                new SortClause\Field('test-type', 'sort_field'),
            ),
        );

        $searchResult = $repository->getSearchService()->findContent(
            new Query($queryProperties),
            $languageSettings
        );

        $this->assertEquals(count($contentDataList), $searchResult->totalCount);

        foreach ($contentDataList as $index => $contentData) {
            list($contentNo, $translationLanguageCode, $indexMap) = $contentData;
            /** @var \eZ\Publish\Api\Repository\Values\Content\Content $content */
            $content = $searchResult->searchHits[$index]->valueObject;

            $this->assertEquals(
                $data[$contentNo]['content']->id,
                $content->id
            );
            $this->assertEquals(
                $this->getIndexName($indexMap),
                $searchResult->searchHits[$index]->index
            );
            $this->assertEquals(
                $translationLanguageCode,
                $searchResult->searchHits[$index]->matchedTranslation
            );
        }
    }

    /**
     * @dataProvider providerForTestFind
     * @depends testCreateTestContent
     *
     * @param array $languageSettings
     * @param array $contentDataList
     * @param array $context
     */
    public function testFindLocationsSingle(
        array $languageSettings,
        array $contentDataList,
        array $context
    ) {
        /** @var \eZ\Publish\Api\Repository\Repository $repository */
        list($repository, $data) = $context;

        $queryProperties = array(
            'filter' => new Criterion\LogicalAnd(
                array(
                    new Criterion\ContentTypeIdentifier('test-type'),
                    new Criterion\Subtree('/1/2/'),
                )
            ),
            'sortClauses' => array(
                new SortClause\Field('test-type', 'sort_field'),
            ),
        );

        $searchResult = $repository->getSearchService()->findLocations(
            new LocationQuery($queryProperties),
            $languageSettings
        );

        $this->assertEquals(count($contentDataList), $searchResult->totalCount);

        foreach ($contentDataList as $index => $contentData) {
            list($contentNo, $translationLanguageCode, $indexMap) = $contentData;
            /** @var \eZ\Publish\Api\Repository\Values\Content\Location $location */
            $location = $searchResult->searchHits[$index]->valueObject;

            $this->assertEquals(
                $data[$contentNo]['locations'][0]->id,
                $location->id
            );
            $this->assertEquals(
                $this->getIndexName($indexMap),
                $searchResult->searchHits[$index]->index
            );
            $this->assertEquals(
                $translationLanguageCode,
                $searchResult->searchHits[$index]->matchedTranslation
            );
        }
    }

    /**
     * @dataProvider providerForTestFind
     * @depends testCreateTestContent
     *
     * @param array $languageSettings
     * @param array $contentDataList
     * @param array $context
     */
    public function testFindLocationsMultiple(
        array $languageSettings,
        array $contentDataList,
        array $context
    ) {
        /** @var \eZ\Publish\Api\Repository\Repository $repository */
        list($repository, $data) = $context;

        $queryProperties = array(
            'filter' => new Criterion\ContentTypeIdentifier('test-type'),
            'sortClauses' => array(
                new SortClause\Location\Depth(Query::SORT_ASC),
                new SortClause\Field('test-type', 'sort_field'),
            ),
        );

        $searchResult = $repository->getSearchService()->findLocations(
            new LocationQuery($queryProperties),
            $languageSettings
        );

        $this->assertEquals(count($contentDataList) * 2, $searchResult->totalCount);

        foreach ($contentDataList as $index => $contentData) {
            list($contentNo, $translationLanguageCode, $indexMap) = $contentData;
            /** @var \eZ\Publish\Api\Repository\Values\Content\Location $location */
            $location = $searchResult->searchHits[$index]->valueObject;

            $this->assertEquals(
                $data[$contentNo]['locations'][0]->id,
                $location->id
            );
            $this->assertEquals(
                $this->getIndexName($indexMap),
                $searchResult->searchHits[$index]->index
            );
            $this->assertEquals(
                $translationLanguageCode,
                $searchResult->searchHits[$index]->matchedTranslation
            );
        }

        foreach ($contentDataList as $index => $contentData) {
            list($contentNo, $translationLanguageCode, $indexMap) = $contentData;
            $realIndex = $index + count($contentDataList);
            /** @var \eZ\Publish\Api\Repository\Values\Content\Location $location */
            $location = $searchResult->searchHits[$realIndex]->valueObject;

            $this->assertEquals(
                $data[$contentNo]['locations'][1]->id,
                $location->id
            );
            $this->assertEquals(
                $this->getIndexName($indexMap),
                $searchResult->searchHits[$realIndex]->index
            );
            $this->assertEquals(
                $translationLanguageCode,
                $searchResult->searchHits[$realIndex]->matchedTranslation
            );
        }
    }
}
