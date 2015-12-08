<?php

/**
 * File containing the SearchServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Tests\SetupFactory\LegacyElasticsearch;
use EzSystems\EzPlatformSolrSearchEngine\Tests\SetupFactory\LegacySetupFactory as LegacySolrSetupFactory;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * Test case for operations in the SearchService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\SearchService
 * @group integration
 * @group search
 */
class SearchServiceTest extends BaseTest
{
    public function getFilterContentSearches()
    {
        $fixtureDir = $this->getFixtureDir();

        return array(
            0 => array(
                array(
                    'filter' => new Criterion\ContentId(
                        array(1, 4, 10)
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'ContentId.php',
            ),
            1 => array(
                array(
                    'filter' => new Criterion\LogicalAnd(
                        array(
                            new Criterion\ContentId(
                                array(1, 4, 10)
                            ),
                            new Criterion\ContentId(
                                array(4, 12)
                            ),
                        )
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'LogicalAnd.php',
            ),
            2 => array(
                array(
                    'filter' => new Criterion\LogicalOr(
                        array(
                            new Criterion\ContentId(
                                array(1, 4, 10)
                            ),
                            new Criterion\ContentId(
                                array(4, 12)
                            ),
                        )
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'LogicalOr.php',
            ),
            3 => array(
                array(
                    'filter' => new Criterion\LogicalAnd(
                        array(
                            new Criterion\ContentId(
                                array(1, 4, 10)
                            ),
                            new Criterion\LogicalNot(
                                new Criterion\ContentId(
                                    array(10, 12)
                                )
                            ),
                        )
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'LogicalNot.php',
            ),
            4 => array(
                array(
                    'filter' => new Criterion\LogicalAnd(
                        array(
                            new Criterion\ContentId(
                                array(1, 4, 10)
                            ),
                            new Criterion\LogicalAnd(
                                array(
                                    new Criterion\LogicalNot(
                                        new Criterion\ContentId(
                                            array(10, 12)
                                        )
                                    ),
                                )
                            ),
                        )
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'LogicalNot.php',
            ),
            5 => array(
                array(
                    'filter' => new Criterion\ContentTypeId(
                        4
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'ContentTypeId.php',
            ),
            6 => array(
                array(
                    'filter' => new Criterion\ContentTypeIdentifier(
                        'user'
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'ContentTypeId.php',
            ),
            7 => array(
                array(
                    'filter' => new Criterion\MatchNone(),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'MatchNone.php',
            ),
            8 => array(
                array(
                    'filter' => new Criterion\ContentTypeGroupId(
                        2
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'ContentTypeGroupId.php',
            ),
            9 => array(
                array(
                    'filter' => new Criterion\DateMetadata(
                        Criterion\DateMetadata::MODIFIED,
                        Criterion\Operator::GT,
                        1343140540
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'DateMetadataGt.php',
            ),
            10 => array(
                array(
                    'filter' => new Criterion\DateMetadata(
                        Criterion\DateMetadata::MODIFIED,
                        Criterion\Operator::GTE,
                        1311154215
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'DateMetadataGte.php',
            ),
            11 => array(
                array(
                    'filter' => new Criterion\DateMetadata(
                        Criterion\DateMetadata::MODIFIED,
                        Criterion\Operator::LTE,
                        1311154215
                    ),
                    'limit' => 10,
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'DateMetadataLte.php',
            ),
            12 => array(
                array(
                    'filter' => new Criterion\DateMetadata(
                        Criterion\DateMetadata::MODIFIED,
                        Criterion\Operator::IN,
                        array(1033920794, 1060695457, 1343140540)
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'DateMetadataIn.php',
            ),
            13 => array(
                array(
                    'filter' => new Criterion\DateMetadata(
                        Criterion\DateMetadata::MODIFIED,
                        Criterion\Operator::BETWEEN,
                        array(1033920776, 1072180276)
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'DateMetadataBetween.php',
            ),
            14 => array(
                array(
                    'filter' => new Criterion\DateMetadata(
                        Criterion\DateMetadata::CREATED,
                        Criterion\Operator::BETWEEN,
                        array(1033920776, 1072180278)
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'DateMetadataCreated.php',
            ),
            15 => array(
                array(
                    'filter' => new Criterion\CustomField(
                        'user_group_name_value_s',
                        Criterion\Operator::EQ,
                        'Members'
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'Field.php',
            ),
            16 => array(
                array(
                    'filter' => new Criterion\CustomField(
                        'user_group_name_value_s',
                        Criterion\Operator::CONTAINS,
                        'Members'
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'Field.php',
            ),
            17 => array(
                array(
                    'filter' => new Criterion\CustomField(
                        'user_group_name_value_s',
                        Criterion\Operator::LT,
                        'Members'
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'CustomFieldLt.php',
            ),
            18 => array(
                array(
                    'filter' => new Criterion\CustomField(
                        'user_group_name_value_s',
                        Criterion\Operator::LTE,
                        'Members'
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'CustomFieldLte.php',
            ),
            19 => array(
                array(
                    'filter' => new Criterion\CustomField(
                        'user_group_name_value_s',
                        Criterion\Operator::GT,
                        'Members'
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'CustomFieldGt.php',
            ),
            20 => array(
                array(
                    'filter' => new Criterion\CustomField(
                        'user_group_name_value_s',
                        Criterion\Operator::GTE,
                        'Members'
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'CustomFieldGte.php',
            ),
            21 => array(
                array(
                    'filter' => new Criterion\CustomField(
                        'user_group_name_value_s',
                        Criterion\Operator::BETWEEN,
                        array('Administrator users', 'Members')
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'CustomFieldBetween.php',
            ),
            22 => array(
                array(
                    'filter' => new Criterion\RemoteId(
                        array('f5c88a2209584891056f987fd965b0ba', 'faaeb9be3bd98ed09f606fc16d144eca')
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'RemoteId.php',
            ),
            23 => array(
                array(
                    'filter' => new Criterion\SectionId(
                        array(2)
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'SectionId.php',
            ),
            24 => array(
                array(
                    'filter' => new Criterion\Field(
                        'name',
                        Criterion\Operator::EQ,
                        'Members'
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'Field.php',
            ),
            25 => array(
                array(
                    'filter' => new Criterion\Field(
                        'name',
                        Criterion\Operator::IN,
                        array('Members', 'Anonymous Users')
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'FieldIn.php',
            ),
            26 => array(
                array(
                    'filter' => new Criterion\DateMetadata(
                        Criterion\DateMetadata::MODIFIED,
                        Criterion\Operator::BETWEEN,
                        array(1033920275, 1033920794)
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'FieldBetween.php',
            ),
            27 => array(
                array(
                    'filter' => new Criterion\LogicalOr(
                        array(
                            new Criterion\Field(
                                'name',
                                Criterion\Operator::EQ,
                                'Members'
                            ),
                            new Criterion\DateMetadata(
                                Criterion\DateMetadata::MODIFIED,
                                Criterion\Operator::BETWEEN,
                                array(1033920275, 1033920794)
                            ),
                        )
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'FieldOr.php',
            ),
            28 => array(
                array(
                    'filter' => new Criterion\Subtree(
                        '/1/5/'
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'Subtree.php',
            ),
            29 => array(
                array(
                    'filter' => new Criterion\LocationId(
                        array(1, 2, 5)
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'LocationId.php',
            ),
            30 => array(
                array(
                    'filter' => new Criterion\ParentLocationId(
                        array(1)
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'ParentLocationId.php',
            ),
            31 => array(
                array(
                    'filter' => new Criterion\LocationRemoteId(
                        array('3f6d92f8044aed134f32153517850f5a', 'f3e90596361e31d496d4026eb624c983')
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'LocationRemoteId.php',
            ),
            32 => array(
                array(
                    // There is no Status Criterion anymore, this should match all published as well
                    'filter' => new Criterion\Subtree(
                        '/1/'
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                    'limit' => 50,
                ),
                $fixtureDir . 'Status.php',
                // Result having the same sort level should be sorted between them to be system independent
                function (&$data) {
                    usort(
                        $data->searchHits,
                        function ($a, $b) {
                            if ($a->score == $b->score) {
                                if ($a->valueObject['id'] == $b->valueObject['id']) {
                                    return 0;
                                }

                                // Order by ascending ID
                                return ($a->valueObject['id'] < $b->valueObject['id']) ? -1 : 1;
                            }

                            // Order by descending score
                            return ($a->score > $b->score) ? -1 : 1;
                        }
                    );
                },
            ),
            33 => array(
                array(
                    'filter' => new Criterion\UserMetadata(
                        Criterion\UserMetadata::MODIFIER,
                        Criterion\Operator::EQ,
                        14
                    ),
                    'sortClauses' => array(
                        new SortClause\ContentId(),
                    ),
                    'limit' => 50,
                ),
                $fixtureDir . 'UserMetadata.php',
            ),
            34 => array(
                array(
                    'filter' => new Criterion\UserMetadata(
                        Criterion\UserMetadata::MODIFIER,
                        Criterion\Operator::IN,
                        array(14)
                    ),
                    'sortClauses' => array(
                        new SortClause\ContentId(),
                    ),
                    'limit' => 50,
                ),
                $fixtureDir . 'UserMetadata.php',
            ),
            35 => array(
                array(
                    'filter' => new Criterion\UserMetadata(
                        Criterion\UserMetadata::OWNER,
                        Criterion\Operator::EQ,
                        14
                    ),
                    'sortClauses' => array(
                        new SortClause\ContentId(),
                    ),
                    'limit' => 50,
                ),
                $fixtureDir . 'UserMetadata.php',
            ),
            36 => array(
                array(
                    'filter' => new Criterion\UserMetadata(
                        Criterion\UserMetadata::OWNER,
                        Criterion\Operator::IN,
                        array(14)
                    ),
                    'sortClauses' => array(
                        new SortClause\ContentId(),
                    ),
                    'limit' => 50,
                ),
                $fixtureDir . 'UserMetadata.php',
            ),
            37 => array(
                array(
                    'filter' => new Criterion\UserMetadata(
                        Criterion\UserMetadata::GROUP,
                        Criterion\Operator::EQ,
                        12
                    ),
                    'sortClauses' => array(
                        new SortClause\ContentId(),
                    ),
                    'limit' => 50,
                ),
                $fixtureDir . 'UserMetadata.php',
            ),
            38 => array(
                array(
                    'filter' => new Criterion\UserMetadata(
                        Criterion\UserMetadata::GROUP,
                        Criterion\Operator::IN,
                        array(12)
                    ),
                    'sortClauses' => array(
                        new SortClause\ContentId(),
                    ),
                    'limit' => 50,
                ),
                $fixtureDir . 'UserMetadata.php',
            ),
            39 => array(
                array(
                    'filter' => new Criterion\UserMetadata(
                        Criterion\UserMetadata::GROUP,
                        Criterion\Operator::EQ,
                        4
                    ),
                    'sortClauses' => array(
                        new SortClause\ContentId(),
                    ),
                    'limit' => 50,
                ),
                $fixtureDir . 'UserMetadata.php',
            ),
            40 => array(
                array(
                    'filter' => new Criterion\UserMetadata(
                        Criterion\UserMetadata::GROUP,
                        Criterion\Operator::IN,
                        array(4)
                    ),
                    'sortClauses' => array(
                        new SortClause\ContentId(),
                    ),
                    'limit' => 50,
                ),
                $fixtureDir . 'UserMetadata.php',
            ),
            41 => array(
                array(
                    'filter' => new Criterion\Ancestor(
                        array(
                            '/1/5/44/',
                            '/1/5/44/45/',
                        )
                    ),
                    'sortClauses' => array(
                        new SortClause\ContentId(),
                    ),
                    'limit' => 50,
                ),
                $fixtureDir . 'AncestorContent.php',
            ),
        );
    }

    public function getContentQuerySearches()
    {
        $fixtureDir = $this->getFixtureDir();

        return array(
            array(
                array(
                    'filter' => new Criterion\ContentId(
                        array(58, 10)
                    ),
                    'query' => new Criterion\FullText('contact'),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'FullTextFiltered.php',
            ),
            array(
                array(
                    'query' => new Criterion\FullText(
                        'contact',
                        array(
                            'boost' => array(
                                'title' => 2,
                            ),
                            'fuzziness' => .5,
                        )
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'FullText.php',
            ),
            array(
                array(
                    'query' => new Criterion\FullText(
                        'Contact*'
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'FullTextWildcard.php',
            ),
            array(
                array(
                    'query' => new Criterion\LanguageCode('eng-GB', false),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'LanguageCode.php',
            ),
            array(
                array(
                    'query' => new Criterion\LanguageCode(array('eng-US', 'eng-GB')),
                    'offset' => 10,
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'LanguageCodeIn.php',
            ),
            array(
                array(
                    'query' => new Criterion\LanguageCode('eng-GB'),
                    'offset' => 10,
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'LanguageCodeAlwaysAvailable.php',
            ),
            array(
                array(
                    'query' => new Criterion\Visibility(
                        Criterion\Visibility::VISIBLE
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                    'limit' => 50,
                ),
                $fixtureDir . 'Visibility.php',
            ),
        );
    }

    public function getLocationQuerySearches()
    {
        $fixtureDir = $this->getFixtureDir();

        return array(
            array(
                array(
                    'query' => new Criterion\Location\Depth(Criterion\Operator::EQ, 1),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'Depth.php',
            ),
            array(
                array(
                    'query' => new Criterion\Location\Depth(Criterion\Operator::IN, array(1, 3)),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'DepthIn.php',
            ),
            array(
                array(
                    'query' => new Criterion\Location\Depth(Criterion\Operator::GT, 2),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'DepthGt.php',
            ),
            array(
                array(
                    'query' => new Criterion\Location\Depth(Criterion\Operator::GTE, 2),
                    'sortClauses' => array(new SortClause\ContentId()),
                    'limit' => 50,
                ),
                $fixtureDir . 'DepthGte.php',
            ),
            array(
                array(
                    'query' => new Criterion\Location\Depth(Criterion\Operator::LT, 2),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'Depth.php',
            ),
            array(
                array(
                    'query' => new Criterion\Location\Depth(Criterion\Operator::LTE, 2),
                    'sortClauses' => array(new SortClause\ContentId()),
                    'limit' => 50,
                ),
                $fixtureDir . 'DepthLte.php',
            ),
            array(
                array(
                    'query' => new Criterion\Location\Depth(Criterion\Operator::BETWEEN, array(1, 2)),
                    'sortClauses' => array(new SortClause\ContentId()),
                    'limit' => 50,
                ),
                $fixtureDir . 'DepthLte.php',
            ),
            array(
                array(
                    'filter' => new Criterion\Ancestor('/1/5/44/45/'),
                    'sortClauses' => array(
                        new SortClause\Location\Depth(),
                    ),
                    'limit' => 50,
                ),
                $fixtureDir . 'AncestorLocation.php',
            ),
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @dataProvider getFilterContentSearches
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testFindContentFiltered($queryData, $fixture, $closure = null)
    {
        $query = new Query($queryData);
        $this->assertQueryFixture($query, $fixture, $closure);
    }

    /**
     * Test for the findContentInfo() method.
     *
     * @dataProvider getFilterContentSearches
     * @see \eZ\Publish\API\Repository\SearchService::findContentInfo()
     */
    public function testFindContentInfoFiltered($queryData, $fixture, $closure = null)
    {
        $query = new Query($queryData);
        $this->assertQueryFixture($query, $fixture, $this->getContentInfoFixtureClosure($closure), true);
    }

    /**
     * Test for the findLocations() method.
     *
     * @dataProvider getFilterContentSearches
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     */
    public function testFindLocationsContentFiltered($queryData, $fixture, $closure = null)
    {
        $query = new LocationQuery($queryData);
        $this->assertQueryFixture($query, $fixture, $closure);
    }

    /**
     * Test for deprecated $criterion property on query object.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @deprecated
     */
    public function testDeprecatedCriteriaProperty()
    {
        $this->assertQueryFixture(
            new Query(
                array(
                    'query' => new Criterion\ContentId(
                        array(1, 4, 10)
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                )
            ),
            $this->getFixtureDir() . 'DeprecatedContentIdQuery.php'
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @dataProvider getContentQuerySearches
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testQueryContent($queryData, $fixture, $closure = null)
    {
        $query = new Query($queryData);
        $this->assertQueryFixture($query, $fixture, $closure);
    }

    /**
     * Test for the findContentInfo() method.
     *
     * @dataProvider getContentQuerySearches
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testQueryContentInfo($queryData, $fixture, $closure = null)
    {
        $query = new Query($queryData);
        $this->assertQueryFixture($query, $fixture, $this->getContentInfoFixtureClosure($closure), true);
    }

    /**
     * Test for the findLocations() method.
     *
     * @dataProvider getContentQuerySearches
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     */
    public function testQueryContentLocations($queryData, $fixture, $closure = null)
    {
        $query = new LocationQuery($queryData);
        $this->assertQueryFixture($query, $fixture, $closure);
    }

    /**
     * Test for the findLocations() method.
     *
     * @dataProvider getLocationQuerySearches
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     */
    public function testQueryLocations($queryData, $fixture, $closure = null)
    {
        $query = new LocationQuery($queryData);
        $this->assertQueryFixture($query, $fixture, $closure);
    }

    public function getCaseInsensitiveSearches()
    {
        return array(
            array(
                array(
                    'filter' => new Criterion\Field(
                        'name',
                        Criterion\Operator::EQ,
                        'Members'
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
            ),
            array(
                array(
                    'filter' => new Criterion\Field(
                        'name',
                        Criterion\Operator::EQ,
                        'members'
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
            ),
            array(
                array(
                    'filter' => new Criterion\Field(
                        'name',
                        Criterion\Operator::EQ,
                        'MEMBERS'
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
            ),
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @dataProvider getCaseInsensitiveSearches
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testFindContentFieldFiltersCaseSensitivity($queryData)
    {
        $query = new Query($queryData);
        $this->assertQueryFixture(
            $query,
            $this->getFixtureDir() . 'Field.php'
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @dataProvider getCaseInsensitiveSearches
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     */
    public function testFindLocationsFieldFiltersCaseSensitivity($queryData)
    {
        $query = new LocationQuery($queryData);
        $this->assertQueryFixture(
            $query,
            $this->getFixtureDir() . 'Field.php'
        );
    }

    public function getRelationFieldFilterContentSearches()
    {
        $fixtureDir = $this->getFixtureDir();

        return array(
            0 => array(
                array(
                    'filter' => new Criterion\FieldRelation(
                        'image',
                        Criterion\Operator::IN,
                        array(1, 4, 10)
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'FieldRelation.php',
            ),
            1 => array(
                array(
                    'filter' => new Criterion\FieldRelation(
                        'image',
                        Criterion\Operator::IN,
                        array(4, 49)
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'FieldRelationAll.php',
            ),
            2 => array(
                array(
                    'filter' => new Criterion\FieldRelation(
                        'image',
                        Criterion\Operator::IN,
                        array(4)
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'FieldRelation.php',
            ),
            3 => array(
                array(
                    'filter' => new Criterion\FieldRelation(
                        'image',
                        Criterion\Operator::CONTAINS,
                        array(1, 4, 10)
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'MatchNone.php',
            ),
            4 => array(
                array(
                    'filter' => new Criterion\FieldRelation(
                        'image',
                        Criterion\Operator::CONTAINS,
                        array(4, 49)
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'MatchNone.php',
            ),
            5 => array(
                array(
                    'filter' => new Criterion\FieldRelation(
                        'image',
                        Criterion\Operator::CONTAINS,
                        array(4)
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                ),
                $fixtureDir . 'FieldRelation.php',
            ),
        );
    }

    /**
     * Purely for creating relation data needed for testFindRelationFieldContentInfoFiltered().
     */
    public function testRelationContentCreation()
    {
        $repository = $this->getRepository();
        $galleryType = $repository->getContentTypeService()->loadContentTypeByIdentifier('gallery');
        $contentService = $repository->getContentService();

        $createStruct = $contentService->newContentCreateStruct($galleryType, 'eng-GB');
        $createStruct->setField('name', 'Image gallery');
        $createStruct->setField('image', 49);// Images folder
        $draft = $contentService->createContent($createStruct);
        $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($galleryType, 'eng-GB');
        $createStruct->setField('name', 'User gallery');
        $createStruct->setField('image', 4);// User folder
        $draft = $contentService->createContent($createStruct);
        $contentService->publishVersion($draft->getVersionInfo());
    }

    /**
     * Test for FieldRelation using findContentInfo() method.
     *
     * @dataProvider getRelationFieldFilterContentSearches
     * @see \eZ\Publish\API\Repository\SearchService::findContentInfo()
     * @depends eZ\Publish\API\Repository\Tests\SearchServiceTest::testRelationContentCreation
     */
    public function testFindRelationFieldContentInfoFiltered($queryData, $fixture, $closure = null)
    {
        $this->getRepository(false);// To make sure repo is setup w/o removing data from testRelationContentCreation
        $query = new Query($queryData);
        $this->assertQueryFixture($query, $fixture, $this->getContentInfoFixtureClosure($closure), true, true, false);
    }

    public function testFindSingle()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $content = $searchService->findSingle(
            new Criterion\ContentId(
                array(4)
            )
        );

        $this->assertEquals(
            4,
            $content->id
        );
    }

    public function testFindNoPerformCount()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $query = new Query();
        $query->performCount = false;
        $query->query = new Criterion\ContentTypeId(
            array(4)
        );

        $searchHit = $searchService->findContent($query);

        if (ltrim(get_class($this->getSetupFactory()), '\\') === 'eZ\Publish\API\Repository\Tests\SetupFactory\Legacy') {
            $this->assertEquals(
                null,
                $searchHit->totalCount
            );
        } else {
            $this->assertEquals(
                2,
                $searchHit->totalCount
            );
        }
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testFindNoPerformCountException()
    {
        if (ltrim(get_class($this->getSetupFactory()), '\\') !== 'eZ\Publish\API\Repository\Tests\SetupFactory\Legacy') {
            $this->markTestSkipped('Only applicable to Legacy/DB based search');
        }

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $query = new Query();
        $query->performCount = false;
        $query->limit = 0;
        $query->query = new Criterion\ContentTypeId(
            array(4)
        );

        $searchService->findContent($query);
    }

    public function testFindLocationsNoPerformCount()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $query = new LocationQuery();
        $query->performCount = false;
        $query->query = new Criterion\ContentTypeId(
            array(4)
        );

        $searchHit = $searchService->findLocations($query);

        if (ltrim(get_class($this->getSetupFactory()), '\\') === 'eZ\Publish\API\Repository\Tests\SetupFactory\Legacy') {
            $this->assertEquals(
                null,
                $searchHit->totalCount
            );
        } else {
            $this->assertEquals(
                2,
                $searchHit->totalCount
            );
        }
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testFindLocationsNoPerformCountException()
    {
        if (ltrim(get_class($this->getSetupFactory()), '\\') !== 'eZ\Publish\API\Repository\Tests\SetupFactory\Legacy') {
            $this->markTestSkipped('Only applicable to Legacy/DB based search');
        }

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $query = new LocationQuery();
        $query->performCount = false;
        $query->limit = 0;
        $query->query = new Criterion\ContentTypeId(
            array(4)
        );

        $searchService->findLocations($query);
    }

    /**
     * Create test Content with ezcountry field having multiple countries selected.
     *
     * @return Content
     */
    protected function createMultipleCountriesContent()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();

        $createStruct = $contentTypeService->newContentTypeCreateStruct('countries-multiple');
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->remoteId = 'countries-multiple-123';
        $createStruct->names = array('eng-GB' => 'Multiple countries');
        $createStruct->creatorId = 14;
        $createStruct->creationDate = new \DateTime();

        $fieldCreate = $contentTypeService->newFieldDefinitionCreateStruct('countries', 'ezcountry');
        $fieldCreate->names = array('eng-GB' => 'Countries');
        $fieldCreate->fieldGroup = 'main';
        $fieldCreate->position = 1;
        $fieldCreate->isTranslatable = false;
        $fieldCreate->isSearchable = true;
        $fieldCreate->fieldSettings = array('isMultiple' => true);

        $createStruct->addFieldDefinition($fieldCreate);

        $contentGroup = $contentTypeService->loadContentTypeGroupByIdentifier('Content');
        $contentTypeDraft = $contentTypeService->createContentType($createStruct, array($contentGroup));
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        $contentType = $contentTypeService->loadContentType($contentTypeDraft->id);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->remoteId = 'countries-multiple-456';
        $createStruct->alwaysAvailable = false;
        $createStruct->setField(
            'countries',
            array('BE', 'DE', 'FR', 'HR', 'NO', 'PT', 'RU')
        );

        $draft = $contentService->createContent($createStruct);
        $content = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        return $content;
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testFieldCollectionContains()
    {
        $testContent = $this->createMultipleCountriesContent();

        $query = new Query(
            array(
                'query' => new Criterion\Field(
                    'countries',
                    Criterion\Operator::CONTAINS,
                    'Belgium'
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $result = $searchService->findContent($query);

        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $testContent->id,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\SearchServiceTest::testFieldCollectionContains
     */
    public function testFieldCollectionContainsNoMatch()
    {
        $this->createMultipleCountriesContent();
        $query = new Query(
            array(
                'query' => new Criterion\Field(
                    'countries',
                    Criterion\Operator::CONTAINS,
                    'Netherlands Antilles'
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $result = $searchService->findContent($query);

        $this->assertEquals(0, $result->totalCount);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument '$criterion->target' is invalid: No searchable fields found for the given criterion target 'some_hopefully_unknown_field'
     */
    public function testInvalidFieldIdentifierRange()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findContent(
            new Query(
                array(
                    'filter' => new Criterion\Field(
                        'some_hopefully_unknown_field',
                        Criterion\Operator::BETWEEN,
                        array(10, 1000)
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                )
            )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument '$criterion->target' is invalid: No searchable fields found for the given criterion target 'some_hopefully_unknown_field'
     */
    public function testInvalidFieldIdentifierIn()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findContent(
            new Query(
                array(
                    'filter' => new Criterion\Field(
                        'some_hopefully_unknown_field',
                        Criterion\Operator::EQ,
                        1000
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                )
            )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument '$criterion->target' is invalid: No searchable fields found for the given criterion target 'tag_cloud_url'
     */
    public function testFindContentWithNonSearchableField()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findContent(
            new Query(
                array(
                    'filter' => new Criterion\Field(
                        'tag_cloud_url',
                        Criterion\Operator::EQ,
                        'http://nimbus.com'
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                )
            )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument '$sortClause->targetData' is invalid: No searchable fields found for the given sort clause target 'title' on 'template_look'
     */
    public function testSortFieldWithNonSearchableField()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findContent(
            new Query(
                array(
                    'sortClauses' => array(new SortClause\Field('template_look', 'title')),
                )
            )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument '$sortClause->targetData' is invalid: No searchable fields found for the given sort clause target 'title' on 'template_look'
     */
    public function testSortMapLocationDistanceWithNonSearchableField()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findContent(
            new Query(
                array(
                    'sortClauses' => array(
                        new SortClause\MapLocationDistance(
                            'template_look',
                            'title',
                            1,
                            2
                        ),
                    ),
                )
            )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindSingleFailMultiple()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findSingle(
            new Criterion\ContentId(
                array(4, 10)
            )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindSingleWithNonSearchableField()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findSingle(
            new Criterion\Field(
                'tag_cloud_url',
                Criterion\Operator::EQ,
                'http://nimbus.com'
            )
        );
    }

    public function getSortedContentSearches()
    {
        $fixtureDir = $this->getFixtureDir();

        return array(
            0 => array(
                array(
                    'filter' => new Criterion\SectionId(array(2)),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(),
                ),
                $fixtureDir . 'SortNone.php',
                // Result having the same sort level should be sorted between them to be system independent
                function (&$data) {
                    usort(
                        $data->searchHits,
                        function ($a, $b) {
                            return ($a->valueObject['id'] < $b->valueObject['id']) ? -1 : 1;
                        }
                    );
                },
            ),
            1 => array(
                array(
                    'filter' => new Criterion\SectionId(array(2)),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(
                        new SortClause\DatePublished(),
                        new SortClause\ContentId(),
                    ),
                ),
                $fixtureDir . 'SortDatePublished.php',
            ),
            2 => array(
                array(
                    'filter' => new Criterion\SectionId(array(2)),
                    'offset' => 0,
                    'limit' => 50,
                    'sortClauses' => array(
                        new SortClause\DateModified(),
                        new SortClause\ContentId(),
                    ),
                ),
                $fixtureDir . 'SortDateModified.php',
            ),
            3 => array(
                array(
                    'filter' => new Criterion\SectionId(array(4, 2, 6, 3)),
                    'offset' => 0,
                    'limit' => 50,
                    'sortClauses' => array(
                        new SortClause\SectionIdentifier(),
                        new SortClause\ContentId(),
                    ),
                ),
                $fixtureDir . 'SortSectionIdentifier.php',
            ),
            4 => array(
                array(
                    'filter' => new Criterion\SectionId(array(4, 2, 6, 3)),
                    'offset' => 0,
                    'limit' => 50,
                    'sortClauses' => array(
                        new SortClause\SectionName(),
                        new SortClause\ContentId(),
                    ),
                ),
                $fixtureDir . 'SortSectionName.php',
            ),
            5 => array(
                array(
                    'filter' => new Criterion\SectionId(array(2, 3)),
                    'offset' => 0,
                    'limit' => 50,
                    'sortClauses' => array(
                        new SortClause\ContentName(),
                        new SortClause\ContentId(),
                    ),
                ),
                $fixtureDir . 'SortContentName.php',
            ),
            6 => array(
                array(
                    'filter' => new Criterion\ContentTypeId(1),
                    'offset' => 0,
                    'limit' => 50,
                    'sortClauses' => array(
                        new SortClause\Field('folder', 'name', Query::SORT_ASC),
                        new SortClause\ContentId(),
                    ),
                ),
                $fixtureDir . 'SortFolderName.php',
            ),
            7 => array(
                array(
                    'filter' => new Criterion\ContentTypeId(array(1, 3)),
                    'offset' => 0,
                    'limit' => 50,
                    'sortClauses' => array(
                        new SortClause\Field('folder', 'name', Query::SORT_ASC),
                        new SortClause\ContentId(),
                    ),
                ),
                $fixtureDir . 'SortFieldMultipleTypes.php',
            ),
            8 => array(
                array(
                    'filter' => new Criterion\ContentTypeId(array(1, 3)),
                    'offset' => 0,
                    'limit' => 50,
                    'sortClauses' => array(
                        new SortClause\Field('folder', 'name', Query::SORT_DESC),
                        new SortClause\ContentId(),
                    ),
                ),
                $fixtureDir . 'SortFieldMultipleTypesReverse.php',
            ),
            9 => array(
                array(
                    'filter' => new Criterion\ContentTypeId(array(1, 3)),
                    'offset' => 3,
                    'limit' => 5,
                    'sortClauses' => array(
                        new SortClause\Field('folder', 'name', Query::SORT_ASC),
                        new SortClause\Field('user', 'first_name', Query::SORT_ASC),
                        new SortClause\ContentId(),
                    ),
                ),
                $fixtureDir . 'SortFieldMultipleTypesSlice.php',
            ),
            10 => array(
                array(
                    'filter' => new Criterion\ContentTypeId(array(1, 3)),
                    'offset' => 3,
                    'limit' => 5,
                    'sortClauses' => array(
                        new SortClause\Field('folder', 'name', Query::SORT_DESC),
                        new SortClause\Field('user', 'first_name', Query::SORT_ASC),
                        new SortClause\ContentId(),
                    ),
                ),
                $fixtureDir . 'SortFieldMultipleTypesSliceReverse.php',
            ),
        );
    }

    public function getSortedLocationSearches()
    {
        $fixtureDir = $this->getFixtureDir();

        return array(
            array(
                array(
                    'filter' => new Criterion\SectionId(array(2)),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(new SortClause\Location\Path(Query::SORT_DESC)),
                ),
                $fixtureDir . 'SortPathString.php',
            ),
            array(
                array(
                    'filter' => new Criterion\SectionId(array(2)),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(new SortClause\Location\Depth(Query::SORT_ASC)),
                ),
                $fixtureDir . 'SortLocationDepth.php',
                // Result having the same sort level should be sorted between them to be system independent
                function (&$data) {
                    // Result with ids:
                    //     4 has depth = 1
                    //     11, 12, 13, 42, 59 have depth = 2
                    //     10, 14 have depth = 3
                    $map = array(
                        4 => 0,
                        11 => 1,
                        12 => 2,
                        13 => 3,
                        42 => 4,
                        59 => 5,
                        10 => 6,
                        14 => 7,
                    );
                    usort(
                        $data->searchHits,
                        function ($a, $b) use ($map) {
                            return ($map[$a->valueObject['id']] < $map[$b->valueObject['id']]) ? -1 : 1;
                        }
                    );
                },
            ),
            array(
                array(
                    'filter' => new Criterion\SectionId(array(3)),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(
                        new SortClause\Location\Path(Query::SORT_DESC),
                        new SortClause\ContentName(Query::SORT_ASC),
                    ),
                ),
                $fixtureDir . 'SortMultiple.php',
            ),
            array(
                array(
                    'filter' => new Criterion\SectionId(array(2)),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(
                        new SortClause\Location\Priority(Query::SORT_DESC),
                        new SortClause\ContentId(),
                    ),
                ),
                $fixtureDir . 'SortDesc.php',
            ),
        );
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
        $createStruct->creationDate = new \DateTime();

        $translatableFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct('integer', 'ezinteger');
        $translatableFieldCreate->names = array('eng-GB' => 'Simple translatable integer field');
        $translatableFieldCreate->fieldGroup = 'main';
        $translatableFieldCreate->position = 1;
        $translatableFieldCreate->isTranslatable = true;
        $translatableFieldCreate->isSearchable = true;

        $createStruct->addFieldDefinition($translatableFieldCreate);

        $nonTranslatableFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct('integer2', 'ezinteger');
        $nonTranslatableFieldCreate->names = array('eng-GB' => 'Simple non-translatable integer field');
        $nonTranslatableFieldCreate->fieldGroup = 'main';
        $nonTranslatableFieldCreate->position = 2;
        $nonTranslatableFieldCreate->isTranslatable = false;
        $nonTranslatableFieldCreate->isSearchable = true;

        $createStruct->addFieldDefinition($nonTranslatableFieldCreate);

        $contentGroup = $contentTypeService->loadContentTypeGroupByIdentifier('Content');
        $contentTypeDraft = $contentTypeService->createContentType($createStruct, array($contentGroup));
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        $contentType = $contentTypeService->loadContentType($contentTypeDraft->id);

        return $contentType;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param int $fieldValue11 Value for translatable field in first language
     * @param int $fieldValue12 Value for translatable field in second language
     * @param int $fieldValue2 Value for non translatable field
     * @param string $mainLanguageCode
     * @param bool $alwaysAvailable
     *
     * @return Content
     */
    protected function createMultilingualContent(
        $contentType,
        $fieldValue11 = null,
        $fieldValue12 = null,
        $fieldValue2 = null,
        $mainLanguageCode = 'eng-GB',
        $alwaysAvailable = false
    ) {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = $alwaysAvailable;
        $createStruct->mainLanguageCode = $mainLanguageCode;
        if ($fieldValue11) {
            $createStruct->setField('integer', $fieldValue11, 'eng-GB');
        }
        if ($fieldValue12) {
            $createStruct->setField('integer', $fieldValue12, 'ger-DE');
        }
        $createStruct->setField('integer2', $fieldValue2, $mainLanguageCode);

        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct(2);
        $draft = $contentService->createContent($createStruct, array($locationCreateStruct));
        $content = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        return $content;
    }

    protected function checkPrioritizedLanguagesSupport()
    {
        $setupFactory = $this->getSetupFactory();
        if ($setupFactory instanceof LegacyElasticsearch) {
            $this->markTestIncomplete('Prioritized languages are not supported with Elasticsearch engine');
        }
    }

    public function providerForTestMultilingualFieldSort()
    {
        return array(
            0 => array(
                array(
                    1 => array(1, 2, 1),
                    2 => array(2, 1, 2),
                    3 => array(2, 1, 3),
                    4 => array(1, 2, 4),
                ),
                array(
                    'languages' => array(
                        'eng-GB',
                        'ger-DE',
                    ),
                ),
                array(
                    new SortClause\Field('test-type', 'integer', Query::SORT_ASC),
                    new SortClause\Field('test-type', 'integer2', Query::SORT_DESC),
                ),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 4, 1, 2, 4
                 * Content 1, 1, 2, 1
                 * Content 3, 2, 1, 3
                 * Content 2, 2, 1, 2
                 */
                array(4, 1, 3, 2),
            ),
            1 => array(
                array(
                    1 => array(1, 2, 1),
                    2 => array(2, 1, 2),
                    3 => array(2, 1, 3),
                    4 => array(1, 2, 4),
                ),
                array(
                    'languages' => array(
                        'ger-DE',
                        'eng-GB',
                    ),
                ),
                array(
                    new SortClause\Field('test-type', 'integer', Query::SORT_ASC),
                    new SortClause\Field('test-type', 'integer2', Query::SORT_DESC),
                ),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 3, 2, 1, 3
                 * Content 2, 2, 1, 2
                 * Content 4, 1, 2, 4
                 * Content 1, 1, 2, 1
                 */
                array(3, 2, 4, 1),
            ),
            2 => array(
                array(
                    1 => array(null, 2, null, 'ger-DE'),
                    2 => array(3, null, null, 'eng-GB'),
                    3 => array(4, null, null, 'eng-GB'),
                    4 => array(null, 1, null, 'ger-DE'),
                ),
                array(
                    'languages' => array(
                        'eng-GB',
                    ),
                ),
                array(
                    new SortClause\Field('test-type', 'integer', Query::SORT_DESC),
                ),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 3, 4, -
                 * Content 2, 3, -
                 */
                array(3, 2),
            ),
            3 => array(
                array(
                    1 => array(null, 2, null, 'ger-DE'),
                    2 => array(3, null, null, 'eng-GB'),
                    3 => array(4, null, null, 'eng-GB'),
                    4 => array(null, 1, null, 'ger-DE'),
                ),
                array(
                    'languages' => array(
                        'ger-DE',
                    ),
                ),
                array(
                    new SortClause\Field('test-type', 'integer', Query::SORT_DESC),
                ),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 1, -, 2
                 * Content 4, -, 1
                 */
                array(1, 4),
            ),
            4 => array(
                array(
                    1 => array(null, 2, null, 'ger-DE'),
                    2 => array(3, null, null, 'eng-GB'),
                    3 => array(4, null, null, 'eng-GB'),
                    4 => array(null, 1, null, 'ger-DE'),
                ),
                array(
                    'languages' => array(
                        'eng-GB',
                        'ger-DE',
                    ),
                ),
                array(
                    new SortClause\Field('test-type', 'integer', Query::SORT_DESC),
                ),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 3, 4, -
                 * Content 2, 3, -
                 * Content 1, -, 2
                 * Content 4, -, 1
                 */
                array(3, 2, 1, 4),
            ),
            5 => array(
                array(
                    1 => array(null, 2, null, 'ger-DE'),
                    2 => array(3, null, null, 'eng-GB'),
                    3 => array(4, null, null, 'eng-GB'),
                    4 => array(null, 1, null, 'ger-DE'),
                ),
                array(
                    'languages' => array(
                        'ger-DE',
                        'eng-GB',
                    ),
                ),
                array(
                    new SortClause\Field('test-type', 'integer', Query::SORT_DESC),
                ),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 3, 4, -
                 * Content 2, 3, -
                 * Content 1, -, 2
                 * Content 4, -, 1
                 */
                array(3, 2, 1, 4),
            ),
            6 => array(
                array(
                    1 => array(null, 2, null, 'ger-DE'),
                    2 => array(3, 4, null, 'eng-GB'),
                    3 => array(4, 3, null, 'eng-GB'),
                    4 => array(null, 1, null, 'ger-DE'),
                ),
                array(
                    'languages' => array(
                        'eng-GB',
                        'ger-DE',
                    ),
                ),
                array(
                    new SortClause\Field('test-type', 'integer', Query::SORT_DESC),
                ),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 3, 4, 3
                 * Content 2, 3, 4
                 * Content 1, -, 2
                 * Content 4, -, 1
                 */
                array(3, 2, 1, 4),
            ),
            7 => array(
                array(
                    1 => array(null, 2, null, 'ger-DE'),
                    2 => array(3, 4, null, 'eng-GB'),
                    3 => array(4, 3, null, 'eng-GB'),
                    4 => array(null, 1, null, 'ger-DE'),
                ),
                array(
                    'languages' => array(
                        'ger-DE',
                        'eng-GB',
                    ),
                ),
                array(
                    new SortClause\Field('test-type', 'integer', Query::SORT_DESC),
                ),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 2, 3, 4
                 * Content 3, 4, 3
                 * Content 1, -, 2
                 * Content 4, -, 1
                 */
                array(2, 3, 1, 4),
            ),
            8 => array(
                array(
                    1 => array(null, 1, null, 'ger-DE', true),
                    2 => array(4, null, null, 'eng-GB', true),
                    3 => array(3, null, null, 'eng-GB', false),
                    4 => array(null, 2, null, 'ger-DE', false),
                ),
                array(
                    'languages' => array(
                        'eng-GB',
                    ),
                ),
                array(
                    new SortClause\Field('test-type', 'integer', Query::SORT_ASC),
                ),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 1, -, 1
                 * Content 3, 3, -
                 * Content 2, 4, -
                 */
                array(1, 3, 2),
            ),
            9 => array(
                array(
                    1 => array(null, 1, null, 'ger-DE', true),
                    2 => array(4, null, null, 'eng-GB', true),
                    3 => array(3, null, null, 'eng-GB', false),
                    4 => array(null, 2, null, 'ger-DE', false),
                ),
                array(
                    'languages' => array(
                        'ger-DE',
                    ),
                ),
                array(
                    new SortClause\Field('test-type', 'integer', Query::SORT_DESC),
                ),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 2, 4, -
                 * Content 4, -, 2
                 * Content 1, -, 1
                 */
                array(2, 4, 1),
            ),
            10 => array(
                array(
                    1 => array(null, 1, null, 'ger-DE', true),
                    2 => array(4, null, null, 'eng-GB', true),
                    3 => array(3, null, null, 'eng-GB', false),
                    4 => array(null, 2, null, 'ger-DE', false),
                ),
                array(
                    'languages' => array(
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    new SortClause\Field('test-type', 'integer', Query::SORT_ASC),
                ),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 3, 3, -
                 * Content 2, 4, -
                 */
                array(3, 2),
            ),
            11 => array(
                array(
                    1 => array(null, 1, null, 'ger-DE', true),
                    2 => array(4, null, null, 'eng-GB', true),
                    3 => array(3, null, null, 'eng-GB', false),
                    4 => array(null, 2, null, 'ger-DE', false),
                ),
                array(
                    'languages' => array(
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    new SortClause\Field('test-type', 'integer', Query::SORT_DESC),
                ),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 4, -, 2
                 * Content 1, -, 1
                 */
                array(4, 1),
            ),
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @group rrr
     * @dataProvider providerForTestMultilingualFieldSort
     *
     * @param array $contentDataList
     * @param array $languageSettings
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     * @param array $expected
     */
    public function testMultilingualFieldSortContent(
        array $contentDataList,
        $languageSettings,
        array $sortClauses,
        $expected
    ) {
        $this->assertMultilingualFieldSort(
            $contentDataList,
            $languageSettings,
            $sortClauses,
            $expected
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @group rrr
     * @dataProvider providerForTestMultilingualFieldSort
     *
     * @param array $contentDataList
     * @param array $languageSettings
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     * @param array $expected
     */
    public function testMultilingualFieldSortLocation(
        array $contentDataList,
        $languageSettings,
        array $sortClauses,
        $expected
    ) {
        $this->assertMultilingualFieldSort(
            $contentDataList,
            $languageSettings,
            $sortClauses,
            $expected,
            false
        );
    }

    /**
     * @param array $contentDataList
     * @param array $languageSettings
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     * @param array $expected
     * @param bool $contentSearch
     */
    protected function assertMultilingualFieldSort(
        array $contentDataList,
        $languageSettings,
        array $sortClauses,
        $expected,
        $contentSearch = true
    ) {
        $this->checkPrioritizedLanguagesSupport();
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft($contentType);

        $defaults = array(null, null, null, 'eng-GB', false);
        $contentIdList = array();
        foreach ($contentDataList as $key => $contentData) {
            $contentData = $contentData + $defaults;
            list(
                $fieldValue11,
                $fieldValue12,
                $fieldValue2,
                $mainLanguageCode,
                $alwaysAvailable
            ) = $contentData;

            $contentIdList[$key] = $this->createMultilingualContent(
                $contentType,
                $fieldValue11,
                $fieldValue12,
                $fieldValue2,
                $mainLanguageCode,
                $alwaysAvailable
            )->id;
        }

        // "article" type Content is not matched, this ensures that non-matched
        // field does not affect sort
        $dummySortClause = new SortClause\Field('article', 'title', Query::SORT_ASC);
        array_unshift($sortClauses, $dummySortClause);
        array_push($sortClauses, $dummySortClause);

        $searchService = $repository->getSearchService();
        if ($contentSearch) {
            $query = new Query(
                array(
                    'query' => new Criterion\ContentTypeId($contentType->id),
                    'sortClauses' => $sortClauses,
                )
            );
            $result = $searchService->findContent($query, $languageSettings);
        } else {
            $query = new LocationQuery(
                array(
                    'query' => new Criterion\ContentTypeId($contentType->id),
                    'sortClauses' => $sortClauses,
                )
            );
            $result = $searchService->findLocations($query, $languageSettings);
        }

        $this->assertEquals(count($expected), $result->totalCount);

        $expectedIdList = array();
        foreach ($expected as $contentNumber) {
            $expectedIdList[] = $contentIdList[$contentNumber];
        }

        $this->assertEquals($expectedIdList, $this->mapResultContentIds($result));
    }

    public function providerForTestMultilingualFieldFilter()
    {
        return array(
            0 => array(
                $fixture = array(
                    1 => array(null, 1, null, 'ger-DE', true),
                    2 => array(4, null, null, 'eng-GB', true),
                    3 => array(3, null, null, 'eng-GB', false),
                    4 => array(null, 2, null, 'ger-DE', false),
                    5 => array(5, null, null, 'eng-GB', true),
                ),
                $languageSettings = array(
                    'languages' => array(
                        'ger-DE',
                    ),
                ),
                new Criterion\Field('integer', Criterion\Operator::LT, 5),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 2, 4, -
                 * Content 4, -, 2
                 * Content 1, -, 1
                 */
                array(2, 4, 1),
            ),
            1 => array(
                $fixture,
                array(
                    'languages' => array(
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                new Criterion\Field('integer', Criterion\Operator::LT, 2),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 1, -, 1
                 */
                array(1),
            ),
            2 => array(
                $fixture,
                array(
                    'languages' => array(
                        'eng-GB',
                    ),
                ),
                new Criterion\Field('integer', Criterion\Operator::LTE, 4),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 5, 5, -
                 * Content 2, 4, -
                 * Content 3, 3, -
                 * Content 1, -, 1
                 */
                array(2, 3, 1),
            ),
            3 => array(
                $fixture,
                array(
                    'languages' => array(
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                new Criterion\Field('integer', Criterion\Operator::LTE, 4),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 2, 4, -
                 * Content 3, 3, -
                 */
                array(2, 3),
            ),
            4 => array(
                $fixture,
                $languageSettings,
                new Criterion\Field('integer', Criterion\Operator::LTE, 4),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 2, 4, -
                 * Content 4, -, 2
                 * Content 1, -, 1
                 */
                array(2, 4, 1),
            ),
            5 => array(
                $fixture,
                $languageSettings,
                new Criterion\Field('integer', Criterion\Operator::GT, 1),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 5, 5, -
                 * Content 2, 4, -
                 * Content 4, -, 2
                 */
                array(5, 2, 4),
            ),
            6 => array(
                $fixture,
                $languageSettings,
                new Criterion\Field('integer', Criterion\Operator::GTE, 2),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 5, 5, -
                 * Content 2, 4, -
                 * Content 4, -, 2
                 */
                array(5, 2, 4),
            ),
            7 => array(
                $fixture,
                $languageSettings,
                new Criterion\Field('integer', Criterion\Operator::BETWEEN, array(2, 4)),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 2, 4, -
                 * Content 4, -, 2
                 */
                array(2, 4),
            ),
            8 => array(
                $fixture,
                $languageSettings,
                new Criterion\Field('integer', Criterion\Operator::BETWEEN, array(4, 2)),
                array(),
            ),
            9 => array(
                $fixture,
                $languageSettings,
                new Criterion\Field('integer', Criterion\Operator::EQ, 4),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 4, -, 2
                 */
                array(2),
            ),
            10 => array(
                $fixture,
                $languageSettings,
                new Criterion\Field('integer', Criterion\Operator::EQ, 2),
                /**
                 * Expected order, Value eng-GB, Value ger-DE.
                 *
                 * Content 2, 4, -
                 */
                array(4),
            ),
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @group ttt
     * @dataProvider providerForTestMultilingualFieldFilter
     *
     * @param array $contentDataList
     * @param array $languageSettings
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $expected
     */
    public function testMultilingualFieldFilterContent(
        array $contentDataList,
        $languageSettings,
        Criterion $criterion,
        $expected
    ) {
        $this->assertMultilingualFieldFilter(
            $contentDataList,
            $languageSettings,
            $criterion,
            $expected
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @group ttt
     * @dataProvider providerForTestMultilingualFieldFilter
     *
     * @param array $contentDataList
     * @param array $languageSettings
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $expected
     */
    public function testMultilingualFieldFilterLocation(
        array $contentDataList,
        $languageSettings,
        Criterion $criterion,
        $expected
    ) {
        $this->assertMultilingualFieldFilter(
            $contentDataList,
            $languageSettings,
            $criterion,
            $expected,
            false
        );
    }

    /**
     * @param array $contentDataList
     * @param array $languageSettings
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $expected
     * @param bool $contentSearch
     */
    protected function assertMultilingualFieldFilter(
        array $contentDataList,
        $languageSettings,
        Criterion $criterion,
        $expected,
        $contentSearch = true
    ) {
        $this->checkPrioritizedLanguagesSupport();
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft($contentType);

        $defaults = array(null, null, null, 'eng-GB', false);
        $contentIdList = array();
        foreach ($contentDataList as $key => $contentData) {
            $contentData = $contentData + $defaults;
            list(
                $fieldValue11,
                $fieldValue12,
                $fieldValue2,
                $mainLanguageCode,
                $alwaysAvailable
            ) = $contentData;

            $contentIdList[$key] = $this->createMultilingualContent(
                $contentType,
                $fieldValue11,
                $fieldValue12,
                $fieldValue2,
                $mainLanguageCode,
                $alwaysAvailable
            )->id;
        }

        $sortClause = new SortClause\Field('test-type', 'integer', Query::SORT_DESC);
        $searchService = $repository->getSearchService();
        if ($contentSearch) {
            $query = new Query(
                array(
                    'query' => new Criterion\LogicalAnd(
                        array(
                            new Criterion\ContentTypeId($contentType->id),
                            $criterion,
                        )
                    ),
                    'sortClauses' => array($sortClause),
                )
            );
            $result = $searchService->findContent($query, $languageSettings);
        } else {
            $query = new LocationQuery(
                array(
                    'query' => new Criterion\LogicalAnd(
                        array(
                            new Criterion\ContentTypeId($contentType->id),
                            $criterion,
                        )
                    ),
                    'sortClauses' => array($sortClause),
                )
            );
            $result = $searchService->findLocations($query, $languageSettings);
        }

        $this->assertEquals(count($expected), $result->totalCount);

        $expectedIdList = array();
        foreach ($expected as $contentNumber) {
            $expectedIdList[] = $contentIdList[$contentNumber];
        }

        $this->assertEquals($expectedIdList, $this->mapResultContentIds($result));
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $result
     *
     * @return array
     */
    protected function mapResultContentIds(SearchResult $result)
    {
        return array_map(
            function (SearchHit $searchHit) {
                if ($searchHit->valueObject instanceof Location) {
                    return $searchHit->valueObject->contentInfo->id;
                }

                return $searchHit->valueObject->id;
            },
            $result->searchHits
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @dataProvider getSortedContentSearches
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testFindAndSortContent($queryData, $fixture, $closure = null)
    {
        $query = new Query($queryData);
        $this->assertQueryFixture($query, $fixture, $closure);
    }

    /**
     * Test for the findContentInfo() method.
     *
     * @dataProvider getSortedContentSearches
     * @see \eZ\Publish\API\Repository\SearchService::findContentInfo()
     */
    public function testFindAndSortContentInfo($queryData, $fixture, $closure = null)
    {
        $query = new Query($queryData);
        $this->assertQueryFixture($query, $fixture, $this->getContentInfoFixtureClosure($closure), true);
    }

    /**
     * Test for the findLocations() method.
     *
     * @dataProvider getSortedContentSearches
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     */
    public function testFindAndSortContentLocations($queryData, $fixture, $closure = null)
    {
        $query = new LocationQuery($queryData);
        $this->assertQueryFixture($query, $fixture, $closure);
    }

    /**
     * Test for the findLocations() method.
     *
     * @dataProvider getSortedLocationSearches
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     */
    public function testFindAndSortLocations($queryData, $fixture, $closure = null)
    {
        $query = new LocationQuery($queryData);
        $this->assertQueryFixture($query, $fixture, $closure);
    }

    public function getFacettedSearches()
    {
        $fixtureDir = $this->getFixtureDir();

        return array(
            array(
                new Query(
                    array(
                        'filter' => new Criterion\SectionId(array(1)),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\ContentTypeFacetBuilder(
                                array(
                                    'name' => 'type',
                                )
                            ),
                        ),
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                ),
                $fixtureDir . '/FacetContentType.php',
            ),
            array(
                new Query(
                    array(
                        'filter' => new Criterion\SectionId(array(1)),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\ContentTypeFacetBuilder(
                                array(
                                    'name' => 'type',
                                    'minCount' => 3,
                                )
                            ),
                        ),
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                ),
                $fixtureDir . '/FacetContentTypeMinCount.php',
            ),
            array(
                new Query(
                    array(
                        'filter' => new Criterion\SectionId(array(1)),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\ContentTypeFacetBuilder(
                                array(
                                    'name' => 'type',
                                    'limit' => 5,
                                )
                            ),
                        ),
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                ),
                $fixtureDir . '/FacetContentTypeMinLimit.php',
            ),
            array(
                new Query(
                    array(
                        'filter' => new Criterion\SectionId(array(1)),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\SectionFacetBuilder(
                                array(
                                    'name' => 'section',
                                )
                            ),
                        ),
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                ),
                $fixtureDir . '/FacetSection.php',
            ),
            array(
                new Query(
                    array(
                        'filter' => new Criterion\SectionId(array(1)),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\UserFacetBuilder(
                                array(
                                    'name' => 'creator',
                                )
                            ),
                        ),
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                ),
                $fixtureDir . '/FacetUser.php',
            ),
            array(
                new Query(
                    array(
                        'filter' => new Criterion\SectionId(array(1)),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\TermFacetBuilder(),
                        ),
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                ),
                $fixtureDir . '/FacetTerm.php',
            ),
            /* @todo: It needs to be defined how this one is supposed to work.
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\CriterionFacetBuilder()
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetCriterion.php',
            ), // */
            /* @todo: Add sane ranges here:
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\DateRangeFacetBuilder( array() )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetDateRange.php',
            ), // */
            array(
                new Query(
                    array(
                        'filter' => new Criterion\SectionId(array(1)),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\FieldFacetBuilder(
                                array(
                                    'fieldPaths' => array('article/title'),
                                )
                            ),
                        ),
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                ),
                $fixtureDir . '/FacetFieldSimple.php',
            ),
            array(
                new Query(
                    array(
                        'filter' => new Criterion\SectionId(array(1)),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\FieldFacetBuilder(
                                array(
                                    'fieldPaths' => array('article/title'),
                                    'regex' => '(a|b|c)',
                                )
                            ),
                        ),
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                ),
                $fixtureDir . '/FacetFieldRegexp.php',
            ),
            array(
                new Query(
                    array(
                        'filter' => new Criterion\SectionId(array(1)),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\FieldFacetBuilder(
                                array(
                                    'fieldPaths' => array('article/title'),
                                    'regex' => '(a|b|c)',
                                    'sort' => FacetBuilder\FieldFacetBuilder::TERM_DESC,
                                )
                            ),
                        ),
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                ),
                $fixtureDir . '/FacetFieldRegexpSortTerm.php',
            ),
            array(
                new Query(
                    array(
                        'filter' => new Criterion\SectionId(array(1)),
                        'offset' => 0,
                        'limit' => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\FieldFacetBuilder(
                                array(
                                    'fieldPaths' => array('article/title'),
                                    'regex' => '(a|b|c)',
                                    'sort' => FacetBuilder\FieldFacetBuilder::COUNT_DESC,
                                )
                            ),
                        ),
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                ),
                $fixtureDir . '/FacetFieldRegexpSortCount.php',
            ),
            /* @todo: Add sane ranges here:
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\FieldRangeFacetBuilder( array(
                                'fieldPath' => 'product/price',
                            ) )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetFieldRegexpSortCount.php',
            ), // */
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @dataProvider getFacettedSearches
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testFindFacettedContent(Query $query, $fixture)
    {
        $this->assertQueryFixture($query, $fixture);
    }

    /**
     * Test for the findContentInfo() method.
     *
     * @dataProvider getFacettedSearches
     * @see \eZ\Publish\API\Repository\SearchService::findContentInfo()
     */
    public function testFindFacettedContentInfo(Query $query, $fixture)
    {
        $this->assertQueryFixture($query, $fixture, $this->getContentInfoFixtureClosure(), true);
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testQueryCustomField()
    {
        $query = new Query(
            array(
                'query' => new Criterion\CustomField(
                    'custom_field',
                    Criterion\Operator::EQ,
                    'AdMiNiStRaToR'
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(new SortClause\ContentId()),
            )
        );
        $this->assertQueryFixture(
            $query,
            $this->getFixtureDir() . '/QueryCustomField.php'
        );
    }

    /**
     * Test for the findContent() method.
     *
     * This tests explicitely queries the first_name while user is contained in
     * the last_name of admin and anonymous. This is done to show the custom
     * copy field working.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testQueryModifiedField()
    {
        // Check using get_class since the others extend SetupFactory\Legacy
        if (ltrim(get_class($this->getSetupFactory()), '\\') === 'eZ\Publish\API\Repository\Tests\SetupFactory\Legacy') {
            $this->markTestIncomplete(
                'Custom fields not supported by LegacySE ' .
                '(@todo: Legacy should fallback to just querying normal field so this should be tested here)'
            );
        }

        $query = new Query(
            array(
                'query' => new Criterion\Field(
                    'first_name',
                    Criterion\Operator::EQ,
                    'User'
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(new SortClause\ContentId()),
            )
        );
        $query->query->setCustomField('user', 'first_name', 'custom_field');

        $this->assertQueryFixture(
            $query,
            $this->getFixtureDir() . '/QueryModifiedField.php'
        );
    }

    /**
     * Test for the findContent() method.
     *
     * This tests first explicitly creates sort clause on the 'short_name' which is empty
     * for all Content instances of 'folder' ContentType. Custom sort field is then set
     * to the index storage name of folder's 'name' field, in order to show the custom
     * sort field working.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testSortModifiedField()
    {
        // Check using get_class since the others extend SetupFactory\Legacy
        if (ltrim(get_class($this->getSetupFactory()), '\\') === 'eZ\Publish\API\Repository\Tests\SetupFactory\Legacy') {
            $this->markTestIncomplete(
                'Custom field sort not supported by LegacySE ' .
                '(@todo: Legacy should fallback to just querying normal field so this should be tested here)'
            );
        }

        $sortClause = new SortClause\Field('folder', 'short_name', Query::SORT_ASC);
        $sortClause->setCustomField('folder', 'short_name', 'folder_name_value_s');

        $query = new Query(
            array(
                'filter' => new Criterion\ContentTypeId(1),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(
                    $sortClause,
                    new SortClause\ContentId(),
                ),
            )
        );

        $this->assertQueryFixture(
            $query,
            $this->getFixtureDir() . '/SortFolderName.php'
        );
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function createTestPlaceContentType()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $createStruct = $contentTypeService->newContentTypeCreateStruct('testtype');
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->names = array('eng-GB' => 'Test type');
        $createStruct->creatorId = 14;
        $createStruct->creationDate = new \DateTime();

        $translatableFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct('maplocation', 'ezgmaplocation');
        $translatableFieldCreate->names = array('eng-GB' => 'Map location field');
        $translatableFieldCreate->fieldGroup = 'main';
        $translatableFieldCreate->position = 1;
        $translatableFieldCreate->isTranslatable = false;
        $translatableFieldCreate->isSearchable = true;

        $createStruct->addFieldDefinition($translatableFieldCreate);

        $contentGroup = $contentTypeService->loadContentTypeGroupByIdentifier('Content');
        $contentTypeDraft = $contentTypeService->createContentType($createStruct, array($contentGroup));
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        $contentType = $contentTypeService->loadContentType($contentTypeDraft->id);

        return $contentType;
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @group maplocation
     */
    public function testMapLocationDistanceLessThanOrEqual()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft($contentType);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.894877,
                'longitude' => 15.972699,
                'address' => 'Here be wild boars',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $wildBoars = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.927334,
                'longitude' => 15.934847,
                'address' => 'A lone tree',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $tree = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        $query = new Query(
            array(
                'filter' => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentTypeId($contentType->id),
                        new Criterion\MapLocationDistance(
                            'maplocation',
                            Criterion\Operator::LTE,
                            240,
                            43.756825,
                            15.775074
                        ),
                    )
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(),
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findContent($query);

        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $wildBoars->id,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @group maplocation
     */
    public function testMapLocationDistanceGreaterThanOrEqual()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft($contentType);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.894877,
                'longitude' => 15.972699,
                'address' => 'Here be wild boars',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $wildBoars = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.927334,
                'longitude' => 15.934847,
                'address' => 'A lone tree',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $tree = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        $query = new Query(
            array(
                'filter' => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentTypeId($contentType->id),
                        new Criterion\MapLocationDistance(
                            'maplocation',
                            Criterion\Operator::GTE,
                            240,
                            43.756825,
                            15.775074
                        ),
                    )
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(),
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findContent($query);

        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $tree->id,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @group maplocation
     */
    public function testMapLocationDistanceBetween()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft($contentType);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.894877,
                'longitude' => 15.972699,
                'address' => 'Here be wild boars',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $wildBoars = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.927334,
                'longitude' => 15.934847,
                'address' => 'A lone tree',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $tree = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.903777,
                'longitude' => 15.958788,
                'address' => 'Meadow with mushrooms',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $mushrooms = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        $query = new Query(
            array(
                'filter' => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentTypeId($contentType->id),
                        new Criterion\MapLocationDistance(
                            'maplocation',
                            Criterion\Operator::BETWEEN,
                            array(239, 241),
                            43.756825,
                            15.775074
                        ),
                    )
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(),
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findContent($query);

        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $mushrooms->id,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findContent() method.
     *
     * This tests the distance over the pole. The tests intentionally uses large range,
     * as the flat Earth model used in Legacy Storage Search is not precise for the use case.
     * What is tested here is that outer bounding box is correctly calculated, so that
     * location is not excluded.
     *
     * Range between 222km and 350km shows the magnitude of error between great-circle
     * (always very precise) and flat Earth (very imprecise for this use case) models.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @group maplocation
     */
    public function testMapLocationDistanceBetweenPolar()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft($contentType);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 89,
                'longitude' => -164,
                'address' => 'Polar bear media tower',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $polarBear = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        $query = new Query(
            array(
                'filter' => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentTypeId($contentType->id),
                        new Criterion\MapLocationDistance(
                            'maplocation',
                            Criterion\Operator::BETWEEN,
                            array(221, 350),
                            89,
                            16
                        ),
                    )
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(),
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findContent($query);

        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $polarBear->id,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @group maplocation
     */
    public function testMapLocationDistanceSortAscending()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft($contentType);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.894877,
                'longitude' => 15.972699,
                'address' => 'Here be wild boars',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $wildBoars = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.927334,
                'longitude' => 15.934847,
                'address' => 'A lone tree',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $tree = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.903777,
                'longitude' => 15.958788,
                'address' => 'Meadow with mushrooms',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $mushrooms = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        $wellInVodice = array(
            'latitude' => 43.756825,
            'longitude' => 15.775074,
        );

        $query = new Query(
            array(
                'filter' => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentTypeId($contentType->id),
                        new Criterion\MapLocationDistance(
                            'maplocation',
                            Criterion\Operator::GTE,
                            235,
                            $wellInVodice['latitude'],
                            $wellInVodice['longitude']
                        ),
                    )
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(
                    new SortClause\MapLocationDistance(
                        'testtype',
                        'maplocation',
                        $wellInVodice['latitude'],
                        $wellInVodice['longitude'],
                        Query::SORT_ASC
                    ),
                ),
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findContent($query);

        $this->assertEquals(3, $result->totalCount);
        $this->assertEquals(
            $wildBoars->id,
            $result->searchHits[0]->valueObject->id
        );
        $this->assertEquals(
            $mushrooms->id,
            $result->searchHits[1]->valueObject->id
        );
        $this->assertEquals(
            $tree->id,
            $result->searchHits[2]->valueObject->id
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @group maplocation
     */
    public function testMapLocationDistanceSortDescending()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft($contentType);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.894877,
                'longitude' => 15.972699,
                'address' => 'Here be wild boars',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $wildBoars = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.927334,
                'longitude' => 15.934847,
                'address' => 'A lone tree',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $tree = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.903777,
                'longitude' => 15.958788,
                'address' => 'Meadow with mushrooms',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $mushrooms = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        $well = array(
            'latitude' => 43.756825,
            'longitude' => 15.775074,
        );

        $query = new Query(
            array(
                'filter' => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentTypeId($contentType->id),
                        new Criterion\MapLocationDistance(
                            'maplocation',
                            Criterion\Operator::GTE,
                            235,
                            $well['latitude'],
                            $well['longitude']
                        ),
                    )
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(
                    new SortClause\MapLocationDistance(
                        'testtype',
                        'maplocation',
                        $well['latitude'],
                        $well['longitude'],
                        Query::SORT_DESC
                    ),
                ),
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findContent($query);

        $this->assertEquals(3, $result->totalCount);
        $this->assertEquals(
            $wildBoars->id,
            $result->searchHits[2]->valueObject->id
        );
        $this->assertEquals(
            $mushrooms->id,
            $result->searchHits[1]->valueObject->id
        );
        $this->assertEquals(
            $tree->id,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @group maplocation
     */
    public function testMapLocationDistanceWithCustomField()
    {
        $setupFactory = $this->getSetupFactory();
        if ($setupFactory instanceof LegacyElasticsearch) {
            $this->markTestIncomplete("TODO: Some issues with 'copy_to' and 'geo_point'");
        }

        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft($contentType);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.894877,
                'longitude' => 15.972699,
                'address' => 'Here be wild boars',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $wildBoars = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.927334,
                'longitude' => 15.934847,
                'address' => 'A lone tree',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $tree = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        $distanceCriterion = new Criterion\MapLocationDistance(
            'maplocation',
            Criterion\Operator::LTE,
            240,
            43.756825,
            15.775074
        );
        $distanceCriterion->setCustomField('testtype', 'maplocation', 'custom_geolocation_field');

        $query = new Query(
            array(
                'filter' => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentTypeId($contentType->id),
                        $distanceCriterion,
                    )
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(),
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findContent($query);

        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $wildBoars->id,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @group maplocation
     */
    public function testMapLocationDistanceWithCustomFieldSort()
    {
        $setupFactory = $this->getSetupFactory();
        if ($setupFactory instanceof LegacyElasticsearch) {
            $this->markTestIncomplete("TODO: Some issues with 'copy_to' and 'geo_point'");
        }

        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft($contentType);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.894877,
                'longitude' => 15.972699,
                'address' => 'Here be wild boars',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $wildBoars = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.927334,
                'longitude' => 15.934847,
                'address' => 'A lone tree',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $tree = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            array(
                'latitude' => 45.903777,
                'longitude' => 15.958788,
                'address' => 'Meadow with mushrooms',
            ),
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct);
        $mushrooms = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        $well = array(
            'latitude' => 43.756825,
            'longitude' => 15.775074,
        );

        $sortClause = new SortClause\MapLocationDistance(
            'testtype',
            'maplocation',
            $well['latitude'],
            $well['longitude'],
            Query::SORT_DESC
        );
        $sortClause->setCustomField('testtype', 'maplocation', 'custom_geolocation_field');

        $query = new Query(
            array(
                'filter' => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentTypeId($contentType->id),
                        new Criterion\MapLocationDistance(
                            'maplocation',
                            Criterion\Operator::GTE,
                            235,
                            $well['latitude'],
                            $well['longitude']
                        ),
                    )
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(
                    $sortClause,
                ),
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findContent($query);

        $this->assertEquals(3, $result->totalCount);
        $this->assertEquals(
            $wildBoars->id,
            $result->searchHits[2]->valueObject->id
        );
        $this->assertEquals(
            $mushrooms->id,
            $result->searchHits[1]->valueObject->id
        );
        $this->assertEquals(
            $tree->id,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     */
    public function testFindMainLocation()
    {
        $plainSiteLocationId = 56;
        $designLocationId = 58;
        $partnersContentId = 59;
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();

        // Add secondary Location for "Partners" user group, under "Design" page
        $locationService->createLocation(
            $contentService->loadContentInfo($partnersContentId),
            $locationService->newLocationCreateStruct($designLocationId)
        );

        $this->refreshSearch($repository);

        $query = new LocationQuery(
            array(
                'filter' => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ParentLocationId($designLocationId),
                        new Criterion\Location\IsMainLocation(
                            Criterion\Location\IsMainLocation::MAIN
                        ),
                    )
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(),
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations($query);

        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals($plainSiteLocationId, $result->searchHits[0]->valueObject->id);
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     */
    public function testFindNonMainLocation()
    {
        $designLocationId = 58;
        $partnersContentId = 59;
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();

        // Add secondary Location for "Partners" user group, under "Design" page
        $newLocation = $locationService->createLocation(
            $contentService->loadContentInfo($partnersContentId),
            $locationService->newLocationCreateStruct($designLocationId)
        );

        $this->refreshSearch($repository);

        $query = new LocationQuery(
            array(
                'filter' => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ParentLocationId($designLocationId),
                        new Criterion\Location\IsMainLocation(
                            Criterion\Location\IsMainLocation::NOT_MAIN
                        ),
                    )
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(),
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations($query);

        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals($newLocation->id, $result->searchHits[0]->valueObject->id);
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     */
    public function testSortMainLocationAscending()
    {
        $plainSiteLocationId = 56;
        $designLocationId = 58;
        $partnersContentId = 59;
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();

        // Add secondary Location for "Partners" user group, under "Design" page
        $newLocation = $locationService->createLocation(
            $contentService->loadContentInfo($partnersContentId),
            $locationService->newLocationCreateStruct($designLocationId)
        );

        $this->refreshSearch($repository);

        $query = new LocationQuery(
            array(
                'filter' => new Criterion\ParentLocationId($designLocationId),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(
                    new SortClause\Location\IsMainLocation(
                        LocationQuery::SORT_ASC
                    ),
                ),
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations($query);

        $this->assertEquals(2, $result->totalCount);
        $this->assertEquals($newLocation->id, $result->searchHits[0]->valueObject->id);
        $this->assertEquals($plainSiteLocationId, $result->searchHits[1]->valueObject->id);
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     */
    public function testSortMainLocationDescending()
    {
        $plainSiteLocationId = 56;
        $designLocationId = 58;
        $partnersContentId = 59;
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();

        // Add secondary Location for "Partners" user group, under "Design" page
        $newLocation = $locationService->createLocation(
            $contentService->loadContentInfo($partnersContentId),
            $locationService->newLocationCreateStruct($designLocationId)
        );

        $this->refreshSearch($repository);

        $query = new LocationQuery(
            array(
                'filter' => new Criterion\ParentLocationId($designLocationId),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(
                    new SortClause\Location\IsMainLocation(
                        LocationQuery::SORT_DESC
                    ),
                ),
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations($query);

        $this->assertEquals(2, $result->totalCount);
        $this->assertEquals($plainSiteLocationId, $result->searchHits[0]->valueObject->id);
        $this->assertEquals($newLocation->id, $result->searchHits[1]->valueObject->id);
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     */
    public function testContentWithMultipleLocations()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        $forumType = $contentTypeService->loadContentTypeByIdentifier('forum');

        $createStruct = $contentService->newContentCreateStruct($forumType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->setField('name', 'An awesome duplicate forum');

        $draft = $contentService->createContent($createStruct);
        $content = $contentService->publishVersion($draft->getVersionInfo());

        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct(2);
        $location1 = $locationService->createLocation($content->contentInfo, $locationCreateStruct);
        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct(5);
        $location2 = $locationService->createLocation($content->contentInfo, $locationCreateStruct);

        $this->refreshSearch($repository);

        $query = new LocationQuery(
            array(
                'filter' => new Criterion\ContentId($content->id),
                'sortClauses' => array(
                    new SortClause\Location\Id(LocationQuery::SORT_ASC),
                ),
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations($query);

        $this->assertEquals(2, $result->totalCount);
        $this->assertEquals(
            $location1->id,
            $result->searchHits[0]->valueObject->id
        );
        $this->assertEquals(
            $location2->id,
            $result->searchHits[1]->valueObject->id
        );
    }

    protected function createContentForTestUserMetadataGroupHorizontal()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();
        $userService = $repository->getUserService();
        $administratorUser = $repository->getCurrentUser();
        // ID of the "Administrators" user group in an eZ Publish demo installation
        $administratorsUserGroupId = 12;
        // ID of the "Editors" user group in an eZ Publish demo installation
        $editorsUserGroupId = 13;

        $administratorsUserGroup = $userService->loadUserGroup($administratorsUserGroupId);
        $editorsUserGroup = $userService->loadUserGroup($editorsUserGroupId);

        // Add additional Location for Administrators UserGroup under Editors UserGroup Location
        $locationCreateStruct = $locationService->newLocationCreateStruct(
            $editorsUserGroup->contentInfo->mainLocationId
        );
        $newAdministratorsUserGroupLocation = $locationService->createLocation(
            $administratorsUserGroup->contentInfo,
            $locationCreateStruct
        );

        // Add additional Location for administrator user under newly created UserGroup Location
        $locationCreateStruct = $locationService->newLocationCreateStruct(
            $newAdministratorsUserGroupLocation->id
        );
        $locationService->createLocation(
            $administratorUser->contentInfo,
            $locationCreateStruct
        );

        // Create a Content to be found through Editors UserGroup id.
        // This ensures data is indexed, it could also be done by updating metadata of
        // an existing Content, but slot would need to reindex Content and that should
        // be tested elsewhere (dedicated indexing integration tests, missing ATM).
        $contentType = $contentTypeService->loadContentTypeByIdentifier('folder');

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->setField('name', 'test');

        $locationCreateStruct = $locationService->newLocationCreateStruct(2);
        $draft = $contentService->createContent($createStruct, array($locationCreateStruct));
        $content = $contentService->publishVersion($draft->getVersionInfo());
        $contentTypeService->createContentTypeDraft($contentType);

        $this->refreshSearch($repository);

        return $content;
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testUserMetadataGroupHorizontalFilterContent($queryType = null)
    {
        if ($queryType === null) {
            $queryType = 'filter';
        }

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $editorsUserGroupId = 13;

        $content = $this->createContentForTestUserMetadataGroupHorizontal();

        $criteria = array();
        $setupFactory = $this->getSetupFactory();

        // Do not limit for LSE, as it does not not require reindexing.
        // See explanation below.
        if ($setupFactory instanceof LegacySolrSetupFactory || $setupFactory instanceof LegacyElasticsearch) {
            $criteria[] = new Criterion\ContentTypeIdentifier('folder');
        }

        $criteria[] = new Criterion\UserMetadata(
            Criterion\UserMetadata::GROUP,
            Criterion\Operator::EQ,
            $editorsUserGroupId
        );

        $query = new Query(
            array(
                $queryType => new Criterion\LogicalAnd($criteria),
                'sortClauses' => array(
                    new SortClause\ContentId(),
                ),
                'limit' => 50,
            )
        );

        if ($setupFactory instanceof LegacySolrSetupFactory || $setupFactory instanceof LegacyElasticsearch) {
            $result = $searchService->findContent($query);

            // Administrator User is owned by itself, when additional Locations are added
            // it should be reindexed and its UserGroups will updated, which means it should
            // also be found as a Content of Editors UserGroup. However we do not handle this
            // in slots yet, and also miss SPI methods to do it without using Search (also
            // needed to decouple services), because as indexing is asynchronous Search
            // should not eat its own dog food for reindexing.
            $this->assertEquals(1, $result->totalCount);

            $this->assertEquals(
                $content->id,
                $result->searchHits[0]->valueObject->id
            );
        } else {
            // This is how it should eventually work for all search engines,
            // with required reindexing slots properly implemented.

            $result = $searchService->findContent($query);

            // Assert last hit manually, as id will change because it is created in test
            // and not present it base fixture.
            $foundContent1 = array_pop($result->searchHits);
            $result->totalCount = $result->totalCount - 1;
            $this->assertEquals($content->id, $foundContent1->valueObject->id);

            $this->simplifySearchResult($result);
            $this->assertEquals(
                include $this->getFixtureDir() . '/UserMetadata.php',
                $result,
                'Search results do not match.',
                .1 // Be quite generous regarding delay -- most important for scores
            );
        }
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testUserMetadataGroupHorizontalQueryContent()
    {
        $this->testUserMetadataGroupHorizontalFilterContent('query');
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     */
    public function testUserMetadataGroupHorizontalFilterLocation($queryType = null)
    {
        if ($queryType === null) {
            $queryType = 'filter';
        }

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $editorsUserGroupId = 13;

        $content = $this->createContentForTestUserMetadataGroupHorizontal();

        $criteria = array();
        $setupFactory = $this->getSetupFactory();

        // Do not limit for LSE, as it does not not require reindexing.
        // See explanation below.
        if ($setupFactory instanceof LegacySolrSetupFactory || $setupFactory instanceof LegacyElasticsearch) {
            $criteria[] = new Criterion\ContentTypeIdentifier('folder');
        }

        $criteria[] = new Criterion\UserMetadata(
            Criterion\UserMetadata::GROUP,
            Criterion\Operator::EQ,
            $editorsUserGroupId
        );

        $query = new LocationQuery(
            array(
                $queryType => new Criterion\LogicalAnd($criteria),
                'sortClauses' => array(
                    new SortClause\Location\Id(),
                ),
                'limit' => 50,
            )
        );

        if ($setupFactory instanceof LegacySolrSetupFactory || $setupFactory instanceof LegacyElasticsearch) {
            $result = $searchService->findLocations($query);

            // Administrator User is owned by itself, when additional Locations are added
            // it should be reindexed and its UserGroups will updated, which means it should
            // also be found as a Content of Editors UserGroup. However we do not handle this
            // in slots yet, and also miss SPI methods to do it without using Search (also
            // needed to decouple services), because as indexing is asynchronous Search
            // should not eat its own dog food for reindexing.
            $this->assertEquals(1, $result->totalCount);

            $this->assertEquals(
                $content->contentInfo->mainLocationId,
                $result->searchHits[0]->valueObject->id
            );
        } else {
            // This is how it should eventually work for all search engines,
            // with required reindexing slots properly implemented.

            $result = $searchService->findLocations($query);

            // Assert last two hits manually, as ids will change because they are created
            // in test and not present in base fixture.
            $foundLocation1 = array_pop($result->searchHits);
            $foundLocation2 = array_pop($result->searchHits);
            // Remove additional Administrators UserGroup Location
            array_pop($result->searchHits);
            $result->totalCount = $result->totalCount - 2;
            $this->assertEquals(
                $content->versionInfo->contentInfo->mainLocationId,
                $foundLocation1->valueObject->id
            );
            $this->assertEquals(
                $repository->getCurrentUser()->id,
                $foundLocation2->valueObject->contentId
            );

            $this->simplifySearchResult($result);
            $this->assertEquals(
                include $this->getFixtureDir() . '/UserMetadataLocation.php',
                $result,
                'Search results do not match.',
                .1 // Be quite generous regarding delay -- most important for scores
            );
        }
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     */
    public function testUserMetadataGroupHorizontalQueryLocation()
    {
        $this->testUserMetadataGroupHorizontalFilterLocation('query');
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testLanguageAnalysisSeparateContent()
    {
        $setupFactory = $this->getSetupFactory();
        if (!$setupFactory instanceof LegacyElasticsearch) {
            $this->markTestSkipped('Language analysis is implemented only for Elasticsearch storage');
        }

        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();
        $searchService = $repository->getSearchService();
        $languageService = $repository->getContentLanguageService();

        $languageCreateStruct = $languageService->newLanguageCreateStruct();
        $languageCreateStruct->languageCode = 'rus-RU';
        $languageCreateStruct->name = 'Russian';

        $languageService->createLanguage($languageCreateStruct);

        $contentCreateStruct = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier('folder'),
            'eng-GB'
        );

        $contentCreateStruct->setField('name', 'foxes');

        $englishContent = $contentService->publishVersion(
            $contentService->createContent(
                $contentCreateStruct,
                array($locationService->newLocationCreateStruct(2))
            )->versionInfo
        );

        $contentCreateStruct = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier('folder'),
            'rus-RU'
        );

        $contentCreateStruct->setField('name', 'foxes');

        $russianContent = $contentService->publishVersion(
            $contentService->createContent(
                $contentCreateStruct,
                array($locationService->newLocationCreateStruct(2))
            )->versionInfo
        );

        // Only Content in English should be found, because Content in Russian
        // will not be correctly stemmed
        $query = new Query(
            array(
                'query' => new Criterion\FullText('foxing'),
            )
        );

        $searchResult = $searchService->findContent($query);

        $this->assertEquals(1, $searchResult->totalCount);
        $this->assertEquals($englishContent->id, $searchResult->searchHits[0]->valueObject->id);
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testLanguageAnalysisSameContent()
    {
        $setupFactory = $this->getSetupFactory();
        if (!$setupFactory instanceof LegacyElasticsearch) {
            $this->markTestSkipped('Language analysis is implemented only for Elasticsearch storage');
        }

        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();
        $searchService = $repository->getSearchService();
        $languageService = $repository->getContentLanguageService();

        $languageCreateStruct = $languageService->newLanguageCreateStruct();
        $languageCreateStruct->languageCode = 'rus-RU';
        $languageCreateStruct->name = 'Russian';

        $languageService->createLanguage($languageCreateStruct);

        $contentCreateStruct = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier('folder'),
            'eng-GB'
        );

        $contentCreateStruct->setField('name', 'foxes важнейшими', 'eng-GB');
        $contentCreateStruct->setField('name', 'foxes важнейшими', 'rus-RU');

        $mixedContent = $contentService->publishVersion(
            $contentService->createContent(
                $contentCreateStruct,
                array($locationService->newLocationCreateStruct(2))
            )->versionInfo
        );

        // Content will be found because translation in Russian will be correctly stemmed
        $query = new Query(
            array(
                'query' => new Criterion\FullText('важнее'),
            )
        );

        $searchResult = $searchService->findContent($query);

        $this->assertEquals(1, $searchResult->totalCount);
        $this->assertEquals($mixedContent->id, $searchResult->searchHits[0]->valueObject->id);
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testLanguageAnalysisSameContentNotFound()
    {
        $setupFactory = $this->getSetupFactory();
        if (!$setupFactory instanceof LegacyElasticsearch) {
            $this->markTestSkipped('Language analysis is implemented only for Elasticsearch storage');
        }

        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();
        $searchService = $repository->getSearchService();
        $languageService = $repository->getContentLanguageService();

        $languageCreateStruct = $languageService->newLanguageCreateStruct();
        $languageCreateStruct->languageCode = 'rus-RU';
        $languageCreateStruct->name = 'Russian';

        $languageService->createLanguage($languageCreateStruct);

        $contentCreateStruct = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier('folder'),
            'eng-GB'
        );

        $contentCreateStruct->setField('name', 'foxes важнейшими', 'eng-GB');
        $contentCreateStruct->setField('name', 'foxes важнейшими', 'rus-RU');

        $mixedContent = $contentService->publishVersion(
            $contentService->createContent(
                $contentCreateStruct,
                array($locationService->newLocationCreateStruct(2))
            )->versionInfo
        );

        // Content should be found because translation in Russian will be correctly stemmed
        $query = new Query(
            array(
                'query' => new Criterion\FullText('важнее'),
            )
        );

        // Filtering fields for only English will cause no match because the term will
        // not be correctly stemmed
        $searchResult = $searchService->findContent($query, array('languages' => array('eng-GB')));

        $this->assertEquals(0, $searchResult->totalCount);
    }

    /**
     * Assert that query result matches the given fixture.
     *
     * @param Query $query
     * @param string $fixture
     * @param null|callable $closure
     * @param bool $info
     * @param bool $id
     */
    protected function assertQueryFixture(Query $query, $fixture, $closure = null, $ignoreScore = true, $info = false, $id = true)
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        try {
            if ($query instanceof LocationQuery) {
                $setupFactory = $this->getSetupFactory();
                if ($setupFactory instanceof LegacySolrSetupFactory) {
                    // @todo When we want to test score again by default we will need fixtures for Solr
                }

                if ($setupFactory instanceof LegacyElasticsearch) {
                    $position = strrpos($fixture, '/');
                    $fixture = substr_replace($fixture, '/Location', $position, 0);
                }

                $result = $searchService->findLocations($query);
            } elseif ($query instanceof Query) {
                if ($info) {
                    $result = $searchService->findContentInfo($query);
                } else {
                    $result = $searchService->findContent($query);
                }
            } else {
                $this->fail('Expected instance of LocationQuery or Query, got: ' . gettype($query));
            }
            $this->simplifySearchResult($result);
        } catch (NotImplementedException $e) {
            $this->markTestSkipped(
                'This feature is not supported by the current search backend: ' . $e->getMessage()
            );
        }

        if (!is_file($fixture)) {
            if (isset($_ENV['ez_tests_record'])) {
                file_put_contents(
                    $record = $fixture . '.recording',
                    "<?php\n\nreturn " . var_export($result, true) . ";\n\n"
                );
                $this->markTestIncomplete("No fixture available. Result recorded at $record. Result: \n" . $this->printResult($result));
            } else {
                $this->markTestIncomplete("No fixture available. Set \$_ENV['ez_tests_record'] to generate it.");
            }
        }

        $fixture = include $fixture;

        if ($closure !== null) {
            $closure($fixture);
            $closure($result);
        }

        if ($ignoreScore) {
            foreach (array($fixture, $result) as $set) {
                $property = new \ReflectionProperty(get_class($set), 'maxScore');
                $property->setAccessible(true);
                $property->setValue($set, 0.0);

                foreach ($set->searchHits as $hit) {
                    $property = new \ReflectionProperty(get_class($hit), 'score');
                    $property->setAccessible(true);
                    $property->setValue($hit, 0.0);
                }
            }
        }

        foreach (array($fixture, $result) as $set) {
            foreach ($set->searchHits as $hit) {
                $property = new \ReflectionProperty(get_class($hit), 'index');
                $property->setAccessible(true);
                $property->setValue($hit, null);

                $property = new \ReflectionProperty(get_class($hit), 'matchedTranslation');
                $property->setAccessible(true);
                $property->setValue($hit, null);

                if (!$id) {
                    $hit->valueObject['id'] = null;
                }
            }
        }

        $this->assertEquals(
            $fixture,
            $result,
            'Search results do not match.',
            .99 // Be quite generous regarding delay -- most important for scores
        );
    }

    /**
     * Show a simplified view of the search result for manual introspection.
     *
     * @param SearchResult $result
     *
     * @return string
     */
    protected function printResult(SearchResult $result)
    {
        $printed = '';
        foreach ($result->searchHits as $hit) {
            $printed .= sprintf(" - %s (%s)\n", $hit->valueObject['title'], $hit->valueObject['id']);
        }

        return $printed;
    }

    /**
     * Simplify search result.
     *
     * This leads to saner comparisons of results, since we do not get the full
     * content objects every time.
     *
     * @param SearchResult $result
     */
    protected function simplifySearchResult(SearchResult $result)
    {
        $result->time = 1;

        foreach ($result->searchHits as $hit) {
            switch (true) {
                case $hit->valueObject instanceof Content:
                case $hit->valueObject instanceof Location:
                    $hit->valueObject = array(
                        'id' => $hit->valueObject->contentInfo->id,
                        'title' => $hit->valueObject->contentInfo->name,
                    );
                    break;

                case $hit->valueObject instanceof ContentInfo:
                    $hit->valueObject = array(
                        'id' => $hit->valueObject->id,
                        'title' => $hit->valueObject->name,
                    );
                    break;

                default:
                    throw new \RuntimeException('Unknown search result hit type: ' . get_class($hit->valueObject));
            }
        }
    }

    /**
     * Get fixture directory.
     *
     * @return string
     */
    protected function getFixtureDir()
    {
        return __DIR__ . '/_fixtures/' . getenv('fixtureDir') . '/';
    }

    /**
     * For findContentInfo tests, to reuse fixtures for findContent tests.
     *
     * @param null|callable $closure
     *
     * @return callable
     */
    private function getContentInfoFixtureClosure($closure = null)
    {
        /** @var $data \eZ\Publish\API\Repository\Values\Content\Search\SearchResult */
        return function (&$data) use ($closure) {
            foreach ($data->searchHits as $searchHit) {
                if ($searchHit->valueObject instanceof Content) {
                    $searchHit->valueObject = $searchHit->valueObject->getVersionInfo()->getContentInfo();
                }
            }

            if (isset($closure)) {
                $closure($data);
            }
        };
    }
}
