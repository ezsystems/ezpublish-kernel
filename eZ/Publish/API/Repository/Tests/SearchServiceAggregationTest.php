<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests;

use DateTime;
use DateTimeZone;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\ContentTypeGroupTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\ContentTypeTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\DateMetadataRangeAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\CheckboxTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\CountryTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\DateRangeAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\DateTimeRangeAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\FloatRangeAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\FloatStatsAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\IntegerRangeAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\IntegerStatsAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\KeywordTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\SelectionTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\TimeRangeAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\LanguageTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\ObjectStateTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Range;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\RawRangeAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\RawStatsAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\RawTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\SectionTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\UserMetadataTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\VisibilityTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\MatchAll;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\RangeAggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\RangeAggregationResultEntry;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\StatsAggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResultEntry;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\Core\FieldType\Checkbox\Value as CheckboxValue;
use eZ\Publish\Core\FieldType\Time\Value as TimeValue;

/**
 * Test case for aggregations in the SearchService.
 *
 * @see \eZ\Publish\API\Repository\SearchService
 * @group integration
 * @group search
 * @group aggregations
 */
final class SearchServiceAggregationTest extends BaseTest
{
    private const EXAMPLE_COUNTRY_FIELD_VALUES = [
        ['PL', 'US'],
        ['FR', 'US'],
        ['US'],
        ['GA', 'PL', 'FR'],
        ['FR', 'BE', 'US'],
    ];

    private const EXAMPLE_KEYWORD_FIELD_VALUES = [
        ['foo'],
        ['foo', 'bar'],
        ['foo', 'bar', 'baz'],
    ];

    private const EXAMPLE_SELECTION_FIELD_VALUES = [
        [0],
        [0, 1],
        [0, 1, 2],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $searchService = $this->getRepository()->getSearchService();
        if (!$searchService->supports(SearchService::CAPABILITY_AGGREGATIONS)) {
            self::markTestSkipped("Search engine doesn't support aggregations");
        }
    }

    /**
     * @dataProvider dataProviderForTestAggregation
     */
    public function testFindContentWithAggregation(
        Aggregation $aggregation,
        AggregationResult $expectedResult
    ): void {
        $searchService = $this->getRepository()->getSearchService();

        $query = new Query();
        $query->aggregations[] = $aggregation;
        $query->filter = new MatchAll();
        $query->limit = 0;

        self::assertEquals(
            $expectedResult,
            $searchService->findContent($query)->aggregations->first()
        );
    }

    /**
     * @dataProvider dataProviderForTestAggregation
     */
    public function testFindLocationWithAggregation(
        Aggregation $aggregation,
        AggregationResult $expectedResult
    ): void {
        $searchService = $this->getRepository()->getSearchService();

        $query = new LocationQuery();
        $query->aggregations[] = $aggregation;
        $query->filter = new MatchAll();
        $query->limit = 0;

        self::assertEquals(
            $expectedResult,
            $searchService->findLocations($query)->aggregations->first()
        );
    }

    public function dataProviderForTestAggregation(): iterable
    {
        $contentTypeService = $this->getRepository()->getContentTypeService();

        yield ContentTypeTermAggregation::class => $this->createTermAggregationTestCase(
            new ContentTypeTermAggregation('content_type'),
            [
                'folder' => 6,
                'user_group' => 6,
                'user' => 2,
                'common_ini_settings' => 1,
                'template_look' => 1,
                'feedback_form' => 1,
                'landing_page' => 1,
            ],
            [$contentTypeService, 'loadContentTypeByIdentifier']
        );

        yield ContentTypeGroupTermAggregation::class => $this->createTermAggregationTestCase(
            new ContentTypeGroupTermAggregation('content_type_group'),
            [
                'Content' => 8,
                'Users' => 8,
                'Setup' => 2,
            ],
            [$contentTypeService, 'loadContentTypeGroupByIdentifier']
        );

        $timezone = new DateTimeZone('+0000');

        yield DateMetadataRangeAggregation::class . '::MODIFIED' => [
            new DateMetadataRangeAggregation(
                'modification_date',
                DateMetadataRangeAggregation::MODIFIED,
                [
                    new Range(
                        null,
                        new DateTime('2003-01-01', $timezone)
                    ),
                    new Range(
                        new DateTime('2003-01-01', $timezone),
                        new DateTime('2004-01-01', $timezone)
                    ),
                    new Range(
                        new DateTime('2004-01-01', $timezone),
                        null
                    ),
                ]
            ),
            new RangeAggregationResult(
                'modification_date',
                [
                    new RangeAggregationResultEntry(
                        new Range(
                            null,
                            new DateTime('2003-01-01', $timezone)
                        ),
                        3
                    ),
                    new RangeAggregationResultEntry(
                        new Range(
                            new DateTime('2003-01-01', $timezone),
                            new DateTime('2004-01-01', $timezone)
                        ),
                        3
                    ),
                    new RangeAggregationResultEntry(
                        new Range(
                            new DateTime('2004-01-01', $timezone),
                            null
                        ),
                        12
                    ),
                ]
            ),
        ];

        yield DateMetadataRangeAggregation::class . '::PUBLISHED' => [
            new DateMetadataRangeAggregation(
                'publication_date',
                DateMetadataRangeAggregation::PUBLISHED,
                [
                    new Range(
                        null,
                        new DateTime('2003-01-01', $timezone)
                    ),
                    new Range(
                        new DateTime('2003-01-01', $timezone),
                        new DateTime('2004-01-01', $timezone)
                    ),
                    new Range(
                        new DateTime('2004-01-01', $timezone),
                        null
                    ),
                ]
            ),
            new RangeAggregationResult(
                'publication_date',
                [
                    new RangeAggregationResultEntry(
                        new Range(
                            null,
                            new DateTime('2003-01-01', $timezone)
                        ),
                        6
                    ),
                    new RangeAggregationResultEntry(
                        new Range(
                            new DateTime('2003-01-01', $timezone),
                            new DateTime('2004-01-01', $timezone)
                        ),
                        2
                    ),
                    new RangeAggregationResultEntry(
                        new Range(
                            new DateTime('2004-01-01', $timezone),
                            null
                        ),
                        10
                    ),
                ]
            ),
        ];

        yield LanguageTermAggregation::class => $this->createTermAggregationTestCase(
            new LanguageTermAggregation('language'),
            [
                'eng-US' => 16,
                'eng-GB' => 2,
            ],
            [$this->getRepository()->getContentLanguageService(), 'loadLanguage']
        );

        yield ObjectStateTermAggregation::class => $this->createTermAggregationTestCase(
            new ObjectStateTermAggregation('object_state', 'ez_lock'),
            [
                // TODO: Change the state of some content objects to have better test data
                'not_locked' => 18,
            ],
            function (string $identifier): ObjectState {
                $objectStateService = $this->getRepository()->getObjectStateService();

                static $objectStateGroup = null;
                if ($objectStateGroup === null) {
                    $objectStateGroup = $objectStateService->loadObjectStateGroupByIdentifier('ez_lock');
                }

                return $objectStateService->loadObjectStateByIdentifier($objectStateGroup, $identifier);
            }
        );

        yield SectionTermAggregation::class => $this->createTermAggregationTestCase(
            new SectionTermAggregation('section'),
            [
                'users' => 8,
                'media' => 4,
                'standard' => 2,
                'setup' => 2,
                'design' => 2,
            ],
            [$this->getRepository()->getSectionService(), 'loadSectionByIdentifier']
        );

        $userService = $this->getRepository()->getUserService();

        yield UserMetadataTermAggregation::class . '::OWNER' => $this->createTermAggregationTestCase(
            new UserMetadataTermAggregation('owner', UserMetadataTermAggregation::OWNER),
            [
                'admin' => 18,
            ],
            [$userService, 'loadUserByLogin']
        );

        yield UserMetadataTermAggregation::class . '::GROUP' => $this->createTermAggregationTestCase(
            new UserMetadataTermAggregation('user_group', UserMetadataTermAggregation::GROUP),
            [
                12 => 18,
                14 => 18,
                4 => 18,
            ],
            [$userService, 'loadUserGroup']
        );

        yield UserMetadataTermAggregation::class . '::MODIFIER' => $this->createTermAggregationTestCase(
            new UserMetadataTermAggregation('modifier', UserMetadataTermAggregation::MODIFIER),
            [
                'admin' => 18,
            ],
            [$userService, 'loadUserByLogin']
        );

        yield VisibilityTermAggregation::class => $this->createTermAggregationTestCase(
            new VisibilityTermAggregation('visibility'),
            [
                true => 18,
            ]
        );

        yield RawRangeAggregation::class => [
            new RawRangeAggregation(
                'raw_range',
                'content_version_no_i',
                [
                    new Range(null, 2),
                    new Range(2, 3),
                    new Range(3, null),
                ]
            ),
            new RangeAggregationResult(
                'raw_range',
                [
                    new RangeAggregationResultEntry(new Range(null, 2), 14),
                    new RangeAggregationResultEntry(new Range(2, 3), 3),
                    new RangeAggregationResultEntry(new Range(3, null), 1),
                ]
            ),
        ];

        yield RawStatsAggregation::class => [
            new RawStatsAggregation(
                'raw_stats',
                'content_version_no_i'
            ),
            new StatsAggregationResult(
                'raw_stats',
                18,
                1.0,
                4.0,
                1.3333333333333333,
                24.0
            ),
        ];

        yield RawTermAggregation::class => [
            new RawTermAggregation(
                'raw_term',
                'content_section_identifier_id'
            ),
            new TermAggregationResult('raw_term', [
                new TermAggregationResultEntry('users', 8),
                new TermAggregationResultEntry('media', 4),
                new TermAggregationResultEntry('design', 2),
                new TermAggregationResultEntry('setup', 2),
                new TermAggregationResultEntry('standard', 2),
            ]),
        ];
    }

    /**
     * @dataProvider dataProviderForTestFieldAggregation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation|\eZ\Publish\API\Repository\Values\Content\Query\Aggregation\FieldAggregation $aggregation
     */
    public function testFindContentWithFieldAggregation(
        Aggregation $aggregation,
        string $fieldTypeIdentifier,
        iterable $fieldValues,
        AggregationResult $expectedResult,
        ?callable $configureFieldDefinitionCreateStruct = null
    ): void {
        $this->createFieldAggregationFixtures(
            $this->createContentTypeForFieldAggregation(
                $aggregation->getContentTypeIdentifier(),
                $aggregation->getFieldDefinitionIdentifier(),
                $fieldTypeIdentifier,
                $configureFieldDefinitionCreateStruct
            ),
            $aggregation->getFieldDefinitionIdentifier(),
            $fieldValues
        );

        $searchService = $this->getRepository()->getSearchService();

        $query = new Query();
        $query->aggregations[] = $aggregation;
        $query->filter = new ContentTypeIdentifier($aggregation->getContentTypeIdentifier());
        $query->limit = 0;

        self::assertEquals(
            $expectedResult,
            $searchService->findContent($query)->aggregations->first()
        );
    }

    /**
     * @dataProvider dataProviderForTestFieldAggregation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation|\eZ\Publish\API\Repository\Values\Content\Query\Aggregation\FieldAggregation $aggregation
     */
    public function testFindLocationWithFieldAggregation(
        Aggregation $aggregation,
        string $fieldTypeIdentifier,
        iterable $fieldValues,
        AggregationResult $expectedResult,
        ?callable $configureFieldDefinitionCreateStruct = null
    ): void {
        $this->createFieldAggregationFixtures(
            $this->createContentTypeForFieldAggregation(
                $aggregation->getContentTypeIdentifier(),
                $aggregation->getFieldDefinitionIdentifier(),
                $fieldTypeIdentifier,
                $configureFieldDefinitionCreateStruct
            ),
            $aggregation->getFieldDefinitionIdentifier(),
            $fieldValues
        );

        $searchService = $this->getRepository()->getSearchService();

        $query = new LocationQuery();
        $query->aggregations[] = $aggregation;
        $query->filter = new ContentTypeIdentifier($aggregation->getContentTypeIdentifier());
        $query->limit = 0;

        self::assertEquals(
            $expectedResult,
            $searchService->findLocations($query)->aggregations->first()
        );
    }

    public function dataProviderForTestFieldAggregation(): iterable
    {
        yield CheckboxTermAggregation::class => [
            new CheckboxTermAggregation('checkbox_term', 'content_type', 'boolean'),
            'ezboolean',
            [
                new CheckboxValue(true),
                new CheckboxValue(true),
                new CheckboxValue(true),
                new CheckboxValue(false),
                new CheckboxValue(false),
            ],
            new TermAggregationResult(
                'checkbox_term',
                [
                    new TermAggregationResultEntry(true, 3),
                    new TermAggregationResultEntry(false, 2),
                ]
            ),
        ];

        yield CountryTermAggregation::class . '::TYPE_NAME' => [
            new CountryTermAggregation(
                'country_term',
                'content_type',
                'country',
                CountryTermAggregation::TYPE_NAME
            ),
            'ezcountry',
            self::EXAMPLE_COUNTRY_FIELD_VALUES,
            new TermAggregationResult(
                'country_term',
                [
                    new TermAggregationResultEntry('Canada', 4),
                    new TermAggregationResultEntry('France', 3),
                    new TermAggregationResultEntry('Poland', 2),
                    new TermAggregationResultEntry('Belgium', 1),
                    new TermAggregationResultEntry('Gabon', 1),
                ]
            ),
            static function (FieldDefinitionCreateStruct $createStruct): void {
                $createStruct->fieldSettings = [
                    'isMultiple' => true,
                ];
            },
        ];

        yield CountryTermAggregation::class . '::TYPE_ALPHA_2' => [
            new CountryTermAggregation(
                'country_term',
                'content_type',
                'country',
                CountryTermAggregation::TYPE_ALPHA_2
            ),
            'ezcountry',
            self::EXAMPLE_COUNTRY_FIELD_VALUES,
            new TermAggregationResult(
                'country_term',
                [
                    new TermAggregationResultEntry('CA', 4),
                    new TermAggregationResultEntry('FR', 3),
                    new TermAggregationResultEntry('PL', 2),
                    new TermAggregationResultEntry('BE', 1),
                    new TermAggregationResultEntry('GA', 1),
                ]
            ),
            static function (FieldDefinitionCreateStruct $createStruct): void {
                $createStruct->fieldSettings = [
                    'isMultiple' => true,
                ];
            },
        ];

        yield CountryTermAggregation::class . '::TYPE_ALPHA_3' => [
            new CountryTermAggregation(
                'country_term',
                'content_type',
                'country',
                CountryTermAggregation::TYPE_ALPHA_3
            ),
            'ezcountry',
            self::EXAMPLE_COUNTRY_FIELD_VALUES,
            new TermAggregationResult(
                'country_term',
                [
                    new TermAggregationResultEntry('CAN', 4),
                    new TermAggregationResultEntry('FRA', 3),
                    new TermAggregationResultEntry('POL', 2),
                    new TermAggregationResultEntry('BEL', 1),
                    new TermAggregationResultEntry('GAB', 1),
                ]
            ),
            static function (FieldDefinitionCreateStruct $createStruct): void {
                $createStruct->fieldSettings = [
                    'isMultiple' => true,
                ];
            },
        ];

        yield CountryTermAggregation::class . '::TYPE_IDC' => [
            new CountryTermAggregation(
                'country_term',
                'content_type',
                'country',
                CountryTermAggregation::TYPE_IDC
            ),
            'ezcountry',
            self::EXAMPLE_COUNTRY_FIELD_VALUES,
            new TermAggregationResult(
                'country_term',
                [
                    new TermAggregationResultEntry(1, 4),
                    new TermAggregationResultEntry(33, 3),
                    new TermAggregationResultEntry(48, 2),
                    new TermAggregationResultEntry(32, 1),
                    new TermAggregationResultEntry(241, 1),
                ]
            ),
            static function (FieldDefinitionCreateStruct $createStruct): void {
                $createStruct->fieldSettings = [
                    'isMultiple' => true,
                ];
            },
        ];

        $timezone = new DateTimeZone('+0000');

        yield DateRangeAggregation::class => [
            new DateRangeAggregation(
                'date_range',
                'content_type',
                'date_field',
                [
                    new Range(
                        null,
                        new DateTime('2020-07-01T00:00:00', $timezone)
                    ),
                    new Range(
                        new DateTime('2020-07-01T00:00:00', $timezone),
                        new DateTime('2020-08-01T00:00:00', $timezone)
                    ),
                    new Range(
                        new DateTime('2020-08-01T00:00:00', $timezone),
                        null
                    ),
                ]
            ),
            'ezdate',
            [
                new DateTime('2020-05-01 00:00:00', $timezone),
                new DateTime('2020-06-30 00:00:00', $timezone),
                new DateTime('2020-06-30 12:00:00', $timezone),
                new DateTime('2020-07-01 00:00:00', $timezone),
                new DateTime('2020-07-01 12:00:00', $timezone),
                new DateTime('2020-07-30 12:00:00', $timezone),
                new DateTime('2020-08-01 00:00:01', $timezone),
                new DateTime('2020-08-01 00:00:02', $timezone),
                new DateTime('2020-08-01 00:00:03', $timezone),
            ],
            new RangeAggregationResult(
                'date_range',
                [
                    new RangeAggregationResultEntry(
                        new Range(
                            null,
                            new DateTime('2020-07-01 00:00:00', $timezone)
                        ),
                        3,
                    ),
                    new RangeAggregationResultEntry(
                        new Range(
                            new DateTime('2020-07-01T00:00:00', $timezone),
                            new DateTime('2020-08-01T00:00:00', $timezone)
                        ),
                        3
                    ),
                    new RangeAggregationResultEntry(
                        new Range(
                            new DateTime('2020-08-01T00:00:00', $timezone),
                            null
                        ),
                        3
                    ),
                ]
            ),
        ];

        yield DateTimeRangeAggregation::class => [
            new DateTimeRangeAggregation(
                'datetime_range',
                'content_type',
                'datetime_field',
                [
                    new Range(
                        null,
                        new DateTime('2020-06-30 00:00:01', $timezone)
                    ),
                    new Range(
                        new DateTime('2020-06-30 12:00:00', $timezone),
                        new DateTime('2020-07-30 00:00:00', $timezone)
                    ),
                    new Range(
                        new DateTime('2020-07-30 00:00:01', $timezone),
                        new DateTime('2020-08-01 00:00:03', $timezone)
                    ),
                ]
            ),
            'ezdatetime',
            [
                new DateTime('2020-05-01 00:00:00', $timezone),
                new DateTime('2020-06-30 00:00:00', $timezone),
                new DateTime('2020-06-30 12:00:00', $timezone),
                new DateTime('2020-07-01 00:00:00', $timezone),
                new DateTime('2020-07-01 12:00:00', $timezone),
                new DateTime('2020-07-30 00:00:00', $timezone),
                new DateTime('2020-07-30 12:00:00', $timezone),
                new DateTime('2020-08-01 00:00:01', $timezone),
                new DateTime('2020-08-01 00:00:02', $timezone),
                new DateTime('2020-08-01 00:00:03', $timezone),
            ],
            new RangeAggregationResult(
                'datetime_range',
                [
                    new RangeAggregationResultEntry(
                        new Range(
                            null,
                            new DateTime('2020-06-30 00:00:01', $timezone)
                        ),
                        2,
                    ),
                    new RangeAggregationResultEntry(
                        new Range(
                            new DateTime('2020-06-30 12:00:00', $timezone),
                            new DateTime('2020-07-30 00:00:00', $timezone)
                        ),
                        3
                    ),
                    new RangeAggregationResultEntry(
                        new Range(
                            new DateTime('2020-07-30 00:00:01', $timezone),
                            new DateTime('2020-08-01 00:00:03', $timezone)
                        ),
                        3
                    ),
                ]
            ),
        ];

        yield FloatStatsAggregation::class => [
            new FloatStatsAggregation('float_stats', 'content_type', 'float'),
            'ezfloat',
            [1.0, 2.5, 2.5, 5.25, 7.75],
            new StatsAggregationResult(
                'float_stats',
                5,
                1.0,
                7.75,
                3.8,
                19.0
            ),
        ];

        yield FloatRangeAggregation::class => [
            new IntegerRangeAggregation('float_range', 'content_type', 'integer', [
                new Range(null, 10.0),
                new Range(10.0, 25.0),
                new Range(25.0, 50.0),
                new Range(50.0, null),
            ]),
            'ezfloat',
            range(1.0, 100.0, 2.5),
            new RangeAggregationResult(
                'float_range',
                [
                    new RangeAggregationResultEntry(new Range(null, 10.0), 4),
                    new RangeAggregationResultEntry(new Range(10.0, 25.0), 6),
                    new RangeAggregationResultEntry(new Range(25, 50), 10),
                    new RangeAggregationResultEntry(new Range(50, null), 20),
                ]
            ),
        ];

        yield IntegerStatsAggregation::class => [
            new IntegerStatsAggregation('integer_stats', 'content_type', 'integer'),
            'ezinteger',
            [1, 2, 3, 5, 8, 13, 21],
            new StatsAggregationResult(
                'integer_stats',
                7,
                1,
                21,
                7.571428571428571,
                53
            ),
        ];

        yield IntegerRangeAggregation::class => [
            new IntegerRangeAggregation('integer_range', 'content_type', 'integer', [
                new Range(null, 10),
                new Range(10, 25),
                new Range(25, 50),
                new Range(50, null),
            ]),
            'ezinteger',
            range(1, 100),
            new RangeAggregationResult(
                'integer_range',
                [
                    new RangeAggregationResultEntry(new Range(null, 10), 9),
                    new RangeAggregationResultEntry(new Range(10, 25), 15),
                    new RangeAggregationResultEntry(new Range(25, 50), 25),
                    new RangeAggregationResultEntry(new Range(50, null), 51),
                ]
            ),
        ];

        yield KeywordTermAggregation::class => [
            new KeywordTermAggregation(
                'keyword_term',
                'content_type',
                'keyword'
            ),
            'ezkeyword',
            self::EXAMPLE_KEYWORD_FIELD_VALUES,
            new TermAggregationResult(
                'keyword_term',
                [
                    new TermAggregationResultEntry('foo', 3),
                    new TermAggregationResultEntry('bar', 2),
                    new TermAggregationResultEntry('baz', 1),
                ]
            ),
        ];

        yield SelectionTermAggregation::class => [
            new SelectionTermAggregation(
                'selection_term',
                'content_type',
                'selection'
            ),
            'ezselection',
            self::EXAMPLE_SELECTION_FIELD_VALUES,
            new TermAggregationResult(
                'selection_term',
                [
                    new TermAggregationResultEntry('foo', 3),
                    new TermAggregationResultEntry('bar', 2),
                    new TermAggregationResultEntry('baz', 1),
                ]
            ),
            static function (FieldDefinitionCreateStruct $createStruct): void {
                $createStruct->fieldSettings = [
                    'isMultiple' => true,
                    'options' => [
                        0 => 'foo',
                        1 => 'bar',
                        2 => 'baz',
                    ],
                ];
            },
        ];

        yield TimeRangeAggregation::class => [
            new TimeRangeAggregation(
                'time_term',
                'content_type',
                'time',
                [
                    new Range(null, mktime(7, 0, 0, 0, 0, 0)),
                    new Range(
                        mktime(7, 0, 0, 0, 0, 0),
                        mktime(12, 0, 0, 0, 0, 0)
                    ),
                    new Range(mktime(12, 0, 0, 0, 0, 0), null),
                ]
            ),
            'eztime',
            [
                new TimeValue(mktime(6, 45, 0, 0, 0, 0)),
                new TimeValue(mktime(7, 0, 0, 0, 0, 0)),
                new TimeValue(mktime(6, 30, 0, 0, 0, 0)),
                new TimeValue(mktime(11, 45, 0, 0, 0, 0)),
                new TimeValue(mktime(16, 00, 0, 0, 0, 0)),
                new TimeValue(mktime(17, 00, 0, 0, 0, 0)),
                new TimeValue(mktime(17, 30, 0, 0, 0, 0)),
            ],
            new RangeAggregationResult(
                'time_term',
                [
                    new RangeAggregationResultEntry(
                        new Range(null, mktime(7, 0, 0, 0, 0, 0)),
                        2
                    ),
                    new RangeAggregationResultEntry(
                        new Range(
                            mktime(7, 0, 0, 0, 0, 0),
                            mktime(12, 0, 0, 0, 0, 0)
                        ),
                        2
                    ),
                    new RangeAggregationResultEntry(
                        new Range(mktime(12, 0, 0, 0, 0, 0), null),
                        3
                    ),
                ]
            ),
        ];
    }

    private function createTermAggregationTestCase(
        Aggregation $aggregation,
        iterable $expectedEntries,
        ?callable $mapper = null
    ): array {
        if ($mapper === null) {
            $mapper = function ($key) {
                return $key;
            };
        }

        $entries = [];
        foreach ($expectedEntries as $key => $count) {
            $entries[] = new TermAggregationResultEntry($mapper($key), $count);
        }

        $expectedResult = TermAggregationResult::createForAggregation($aggregation, $entries);

        return [$aggregation, $expectedResult];
    }

    private function createFieldAggregationFixtures(
        ContentType $contentType,
        string $fieldDefinitionIdentifier,
        iterable $values
    ): void {
        $contentService = $this->getRepository()->getContentService();
        $locationService = $this->getRepository()->getLocationService();

        foreach ($values as $value) {
            $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
            $contentCreateStruct->setField($fieldDefinitionIdentifier, $value);

            $contentService->publishVersion(
                $contentService->createContent(
                    $contentCreateStruct,
                    [
                        $locationService->newLocationCreateStruct(2),
                    ]
                )->versionInfo
            );
        }

        $this->refreshSearch($this->getRepository());
    }

    private function createContentTypeForFieldAggregation(
        string $contentTypeIdentifier,
        string $fieldDefinitionIdentifier,
        string $fieldTypeIdentifier,
        ?callable $configureFieldDefinitionCreateStruct = null
    ): ContentType {
        $contentTypeService = $this->getRepository()->getContentTypeService();

        $contentTypeCreateStruct = $contentTypeService->newContentTypeCreateStruct($contentTypeIdentifier);
        $contentTypeCreateStruct->mainLanguageCode = 'eng-GB';
        $contentTypeCreateStruct->names = [
            'eng-GB' => 'Field aggregation',
        ];

        $fieldDefinitionCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct(
            $fieldDefinitionIdentifier,
            $fieldTypeIdentifier
        );
        $fieldDefinitionCreateStruct->names = [
            'eng-GB' => 'Aggregated field',
        ];
        $fieldDefinitionCreateStruct->isSearchable = true;

        if ($configureFieldDefinitionCreateStruct !== null) {
            $configureFieldDefinitionCreateStruct($fieldDefinitionCreateStruct);
        }

        $contentTypeCreateStruct->addFieldDefinition($fieldDefinitionCreateStruct);

        $contentTypeDraft = $contentTypeService->createContentType(
            $contentTypeCreateStruct,
            [
                $contentTypeService->loadContentTypeGroupByIdentifier('Content'),
            ]
        );

        $contentTypeService->publishContentTypeDraft($contentTypeDraft);

        return $contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
    }
}
