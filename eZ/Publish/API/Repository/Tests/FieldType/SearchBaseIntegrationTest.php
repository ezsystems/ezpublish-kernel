<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\FieldType;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Tests\SetupFactory\Legacy;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator;
use eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field as FieldSortClause;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Tests\SetupFactory\LegacyElasticsearch;

/**
 * Integration test for searching and sorting with Field criterion and Field sort clause.
 *
 * This abstract test case should be used as a base for a specific field type search
 * integration tests. It will first create two Content objects with two distinct field
 * values, then execute a series of tests for Field criterion and Field sort clause,
 * explicitly limited on these two values.
 *
 * Field criterion will be tested for each supported operator in all possible ways,
 * combined with LogicalNot criterion, while Field sort clause will be tested in
 * ascending and descending order.
 *
 * Same set of tests will be executed for each type of search (Content search, Location
 * search), and in case of a criterion separately for a filtering and querying type of
 * Query.
 *
 * To get the test working extend it in a concrete field type test and implement
 * methods:
 *
 * - getValidSearchValueOne()
 * - getValidSearchValueTwo()
 *
 * In the test descriptions Content object created with values One and Two are referred to
 * as Content One, and Content Two. See the descriptions of the abstract declarations of
 * these methods for more details on how to choose proper values.
 *
 * If needed you can override the methods that provide Field criterion target value and
 * which by default fall back to the methods mentioned above:
 *
 * - getSearchTargetValueOne()
 * - getSearchTargetValueTwo()
 *
 * In order to test fields additionally indexed by the field type, provide the required
 * data by overriding method:
 *
 * - getAdditionallyIndexedFieldData()
 *
 * The method must return an array of field data for each additionally indexed field,
 * consisting of field's name, as defined in field type's Indexable definition, value One
 * and value Two, corresponding to the data indexed by getValidSearchValueOne() and
 * getValidSearchValueTwo() methods described above. For example:
 *
 * <code>
 *  array(
 *      array(
 *          'file_size',
 *          1024,
 *          4096
 *      ),
 *      ...
 *  )
 * </code>
 *
 * In order to test full text search, provide the required data by overriding method:
 *
 * - getFullTextIndexedFieldData()
 *
 * This method must return an array of search values, consisting of search strings for
 * value One and value Two, corresponding to the data the field type indexes
 * for full text search from what is provided by getValidSearchValueOne() and
 * getValidSearchValueTwo(). By default the method skips tests, you should override
 * it in the concrete test case as required by the field type. For example:
 *
 * <code>
 *  array(
 *      array(
 *          'one',
 *          'two'
 *      ),
 *      ...
 *  )
 * </code>
 *
 * Note: this test case does not concern itself with testing field filters, behaviour
 * of multiple sort clauses or combination with other criteria. These are tested
 * elsewhere as a general field search cases, which enables keeping this test case
 * simple.
 */
abstract class SearchBaseIntegrationTest extends BaseIntegrationTest
{
    /**
     * Get search field value One.
     *
     * The value must be valid for Content creation.
     *
     * When Field sort clause with ascending order is used on the tested field,
     * Content containing the field with this value must come before the Content
     * with value One.
     *
     * Opposite should be the case when using descending order.
     *
     * @return mixed
     */
    abstract protected function getValidSearchValueOne();

    /**
     * Get search target field value One.
     *
     * Returns the Field criterion target value for the field value One.
     * Default implementation falls back on {@link getValidSearchValueOne}.
     *
     * @return mixed
     */
    protected function getSearchTargetValueOne()
    {
        return $this->getValidSearchValueOne();
    }

    /**
     * Get search field value Two.
     *
     * The value must be valid for Content creation.
     *
     * When Field sort clause with ascending order is used on the tested field,
     * Content containing the field with this value must come after the Content
     * with value One.
     *
     * Opposite should be the case when using descending order.
     *
     * @return mixed
     */
    abstract protected function getValidSearchValueTwo();

    /**
     * Get search target field value Two.
     *
     * Returns the Field criterion target value for the field value Two.
     * Default implementation falls back on {@link getValidSearchValueTwo}.
     *
     * @return mixed
     */
    protected function getSearchTargetValueTwo()
    {
        return $this->getValidSearchValueTwo();
    }

    /**
     * Returns test data for field type's additionally indexed fields.
     *
     * An array of field data is returned for each additionally indexed field,
     * consisting of field's name, as defined in field type's Indexable
     * definition, value One, and value Two, corresponding to the data indexed
     * by {@link getValidSearchValueOne()} and {@link getValidSearchValueTwo()}
     * methods. For example:
     *
     * <code>
     *  array(
     *      array(
     *          'file_size',
     *          1024,
     *          4096
     *      ),
     *      ...
     *  )
     * </code>
     *
     * @return array
     */
    protected function getAdditionallyIndexedFieldData()
    {
        return [];
    }

    /**
     * Returns tests data for full text search.
     *
     * An array of search values is returned, consisting of search strings for
     * value One and value Two, corresponding to the data the field type indexes
     * for full text search from what is provided by {@link getValidSearchValueOne()}
     * and {@link getValidSearchValueTwo()}. By default the tests are skipped here,
     * override in the concrete test case as required by the field type.
     * For example:
     *
     * <code>
     *  array(
     *      array(
     *          'one',
     *          'two'
     *      ),
     *      ...
     *  )
     * </code>
     *
     * @return array
     */
    protected function getFullTextIndexedFieldData()
    {
        $this->markTestSkipped(
            'Skipped by default, override in the concrete test case as required by the field type.'
        );
    }

    public function checkFullTextSupport()
    {
        // Does nothing by default, override in a concrete test case as needed
    }

    /**
     * Overload for field types that does not support wildcards in LIKE Field criteria.
     *
     * E.g. Any field type that needs to be matched as a whole: Email, bool, date/time, (singular) relation, integer.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function supportsLikeWildcard($value)
    {
        if ($this->getSetupFactory() instanceof LegacyElasticsearch) {
            $this->markTestSkipped('Elasticsearch Search Engine does not support Field Criterion LIKE');
        }

        return !is_numeric($value) && !is_bool($value);
    }

    /**
     * Used to control test execution by search engine.
     *
     * WARNING: Using this will block testing on a given search engine for FieldType, if partial limited on LegacySE use:
     * checkCustomFieldsSupport(), checkFullTextSupport(), supportsLikeWildcard(), $legacyUnsupportedOperators, (...).
     */
    protected function checkSearchEngineSupport()
    {
        // Does nothing by default, override in a concrete test case as needed
    }

    protected function checkCustomFieldsSupport()
    {
        if (get_class($this->getSetupFactory()) === Legacy::class) {
            $this->markTestSkipped('Legacy Search Engine does not support custom fields');
        }
    }

    protected function checkLocationFieldSearchSupport()
    {
        if ($this->getSetupFactory() instanceof LegacyElasticsearch) {
            $this->markTestSkipped('Elasticsearch Search Engine does not support custom fields');
        }
    }

    /**
     * Creates and returns content with given $fieldData.
     *
     * @param mixed $fieldData
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createTestSearchContent($fieldData, Repository $repository, $contentType)
    {
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $createStruct->setField('name', 'Test object');
        $createStruct->setField(
            'data',
            $fieldData
        );

        $locationCreateStruct = $locationService->newLocationCreateStruct(2);

        return $contentService->publishVersion(
            $contentService->createContent(
                $createStruct,
                [
                    $locationCreateStruct,
                ]
            )->versionInfo
        );
    }

    /**
     * Creates test Content and Locations and returns the context for subsequent testing.
     *
     * Context consists of repository instance and created Content IDs.
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function testCreateTestContent()
    {
        $repository = $this->getRepository();
        $fieldTypeService = $repository->getFieldTypeService();
        $fieldType = $fieldTypeService->getFieldType($this->getTypeName());

        if (!$fieldType->isSearchable()) {
            $this->markTestSkipped("Field type '{$this->getTypeName()}' is not searchable.");
        }

        $this->checkSearchEngineSupport();

        $contentType = $this->testCreateContentType();

        $context = [
            $repository,
            $this->createTestSearchContent(
                $this->getValidSearchValueOne(),
                $repository,
                $contentType
            )->id,
            $this->createTestSearchContent(
                $this->getValidSearchValueTwo(),
                $repository,
                $contentType
            )->id,
        ];

        $this->refreshSearch($repository);

        return $context;
    }

    /**
     * Provider method for testFind* methods.
     *
     * Do not use directly, use getAdditionallyIndexedFieldData() instead.
     *
     * @return array
     */
    public function findProvider()
    {
        $additionalFields = $this->getAdditionallyIndexedFieldData();
        $additionalFields[] = [
            null,
            $this->getSearchTargetValueOne(),
            $this->getSearchTargetValueTwo(),
        ];
        $templates = [
            [true, true],
            [true, false],
            [false, true],
            [false, false],
        ];

        $fixture = [];

        foreach ($additionalFields as $additionalField) {
            foreach ($templates as $template) {
                $template[] = $additionalField[0];
                array_unshift($template, $additionalField[2]);
                array_unshift($template, $additionalField[1]);

                $fixture[] = $template;
            }
        }

        return $fixture;
    }

    /**
     * Tests search with EQ operator.
     *
     * Simplified representation:
     *
     *     value EQ One
     *
     * The result should contain Content One.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindEqualsOne($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new Field('data', Operator::EQ, $valueOne);

        $this->assertFindResult($context, $criteria, true, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with EQ operator.
     *
     * Simplified representation:
     *
     *     NOT( value EQ One )
     *
     * The result should contain Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotEqualsOne($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new LogicalNot(new Field('data', Operator::EQ, $valueOne));

        $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with EQ operator.
     *
     * Simplified representation:
     *
     *     value EQ Two
     *
     * The result should contain Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindEqualsTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new Field('data', Operator::EQ, $valueTwo);

        $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with EQ operator.
     *
     * Simplified representation:
     *
     *     NOT( value EQ Two )
     *
     * The result should contain Content One.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotEqualsTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new LogicalNot(new Field('data', Operator::EQ, $valueTwo));

        $this->assertFindResult($context, $criteria, true, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with IN operator.
     *
     * Simplified representation:
     *
     *     value IN [One]
     *
     * The result should contain Content One.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindInOne($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new Field('data', Operator::IN, [$valueOne]);

        $this->assertFindResult($context, $criteria, true, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with IN operator.
     *
     * Simplified representation:
     *
     *     NOT( value IN [One] )
     *
     * The result should contain Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotInOne($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new LogicalNot(
            new Field('data', Operator::IN, [$valueOne])
        );

        $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with IN operator.
     *
     * Simplified representation:
     *
     *     value IN [Two]
     *
     * The result should contain Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindInTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new Field('data', Operator::IN, [$valueTwo]);

        $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with IN operator.
     *
     * Simplified representation:
     *
     *     NOT( value IN [Two] )
     *
     * The result should contain Content One.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotInTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new LogicalNot(
            new Field('data', Operator::IN, [$valueTwo])
        );

        $this->assertFindResult($context, $criteria, true, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with IN operator.
     *
     * Simplified representation:
     *
     *     value IN [One,Two]
     *
     * The result should contain both Content One and Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindInOneTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new Field(
            'data',
            Operator::IN,
            [
                $valueOne,
                $valueTwo,
            ]
        );

        $this->assertFindResult($context, $criteria, true, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with IN operator.
     *
     * Simplified representation:
     *
     *     NOT( value IN [One,Two] )
     *
     * The result should be empty.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotInOneTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new LogicalNot(
            new Field(
                'data',
                Operator::IN,
                [
                    $valueOne,
                    $valueTwo,
                ]
            )
        );

        $this->assertFindResult($context, $criteria, false, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with GT operator.
     *
     * Simplified representation:
     *
     *     value GT One
     *
     * The result should contain Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindGreaterThanOne($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new Field('data', Operator::GT, $valueOne);

        $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with GT operator.
     *
     * Simplified representation:
     *
     *     NOT( value GT One )
     *
     * The result should contain Content One.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotGreaterThanOne($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new LogicalNot(new Field('data', Operator::GT, $valueOne));

        $this->assertFindResult($context, $criteria, true, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with GT operator.
     *
     * Simplified representation:
     *
     *     value GT Two
     *
     * The result should be empty.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindGreaterThanTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new Field('data', Operator::GT, $valueTwo);

        $this->assertFindResult($context, $criteria, false, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with GT operator.
     *
     * Simplified representation:
     *
     *     NOT( value GT Two )
     *
     * The result should contain both Content One and Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotGreaterThanTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new LogicalNot(new Field('data', Operator::GT, $valueTwo));

        $this->assertFindResult($context, $criteria, true, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with GTE operator.
     *
     * Simplified representation:
     *
     *     value GTE One
     *
     * The result should contain both Content One and Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindGreaterThanOrEqualOne($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new Field('data', Operator::GTE, $valueOne);

        $this->assertFindResult($context, $criteria, true, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with GTE operator.
     *
     * Simplified representation:
     *
     *     NOT( value GTE One )
     *
     * The result should be empty.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotGreaterThanOrEqual($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new LogicalNot(new Field('data', Operator::GTE, $valueOne));

        $this->assertFindResult($context, $criteria, false, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with GTE operator.
     *
     * Simplified representation:
     *
     *     value GTE Two
     *
     * The result should contain Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindGreaterThanOrEqualTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new Field('data', Operator::GTE, $valueTwo);

        $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with GTE operator.
     *
     * Simplified representation:
     *
     *     NOT( value GTE Two )
     *
     * The result should contain Content One.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotGreaterThanOrEqualTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new LogicalNot(new Field('data', Operator::GTE, $valueTwo));

        $this->assertFindResult($context, $criteria, true, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with LT operator.
     *
     * Simplified representation:
     *
     *     value LT One
     *
     * The result should be empty.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindLowerThanOne($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new Field('data', Operator::LT, $valueOne);

        $this->assertFindResult($context, $criteria, false, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with LT operator.
     *
     * Simplified representation:
     *
     *     NOT( value LT One )
     *
     * The result should contain both Content One and Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotLowerThanOne($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new LogicalNot(new Field('data', Operator::LT, $valueOne));

        $this->assertFindResult($context, $criteria, true, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with LT operator.
     *
     * Simplified representation:
     *
     *     value LT Two
     *
     * The result should contain Content One.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindLowerThanTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new Field('data', Operator::LT, $valueTwo);

        $this->assertFindResult($context, $criteria, true, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with LT operator.
     *
     * Simplified representation:
     *
     *     NOT( value LT Two )
     *
     * The result should contain Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotLowerThanTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new LogicalNot(new Field('data', Operator::LT, $valueTwo));

        $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with LTE operator.
     *
     * Simplified representation:
     *
     *     value LTE One
     *
     * The result should contain Content One.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindLowerThanOrEqualOne($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new Field('data', Operator::LTE, $valueOne);

        $this->assertFindResult($context, $criteria, true, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with LTE operator.
     *
     * Simplified representation:
     *
     *     NOT( value LTE One )
     *
     * The result should contain Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotLowerThanOrEqualOne($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new LogicalNot(new Field('data', Operator::LTE, $valueOne));

        $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with LTE operator.
     *
     * Simplified representation:
     *
     *     value LTE Two
     *
     * The result should contain both Content One and Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindLowerThanOrEqualTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new Field('data', Operator::LTE, $valueTwo);

        $this->assertFindResult($context, $criteria, true, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with LTE operator.
     *
     * Simplified representation:
     *
     *     NOT( value LTE Two )
     *
     * The result should be empty.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotLowerThanOrEqualTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new LogicalNot(new Field('data', Operator::LTE, $valueTwo));

        $this->assertFindResult($context, $criteria, false, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with BETWEEN operator.
     *
     * Simplified representation:
     *
     *     value BETWEEN [One,Two]
     *
     * The result should contain both Content One and Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindBetweenOneTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new Field(
            'data',
            Operator::BETWEEN,
            [
                $valueOne,
                $valueTwo,
            ]
        );

        $this->assertFindResult($context, $criteria, true, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with BETWEEN operator.
     *
     * Simplified representation:
     *
     *     NOT( value BETWEEN [One,Two] )
     *
     * The result should contain both Content One and Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotBetweenOneTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new LogicalNot(
            new Field(
                'data',
                Operator::BETWEEN,
                [
                    $valueOne,
                    $valueTwo,
                ]
            )
        );

        $this->assertFindResult($context, $criteria, false, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with BETWEEN operator.
     *
     * Simplified representation:
     *
     *     value BETWEEN [Two,One]
     *
     * The result should be empty.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindBetweenTwoOne($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new Field(
            'data',
            Operator::BETWEEN,
            [
                $valueTwo,
                $valueOne,
            ]
        );

        $this->assertFindResult($context, $criteria, false, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with BETWEEN operator.
     *
     * Simplified representation:
     *
     *     NOT( value BETWEEN [Two,One] )
     *
     * The result should contain both Content One and Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotBetweenTwoOne($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new LogicalNot(
            new Field(
                'data',
                Operator::BETWEEN,
                [
                    $valueTwo,
                    $valueOne,
                ]
            )
        );

        $this->assertFindResult($context, $criteria, true, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with CONTAINS operator.
     *
     * Simplified representation:
     *
     *     value CONTAINS One
     *
     * The result should contain Content One.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindContainsOne($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new Field('data', Operator::CONTAINS, $valueOne);

        $this->assertFindResult($context, $criteria, true, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with CONTAINS operator.
     *
     * Simplified representation:
     *
     *     NOT( value CONTAINS One )
     *
     * The result should contain Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotContainsOne($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new LogicalNot(new Field('data', Operator::CONTAINS, $valueOne));

        $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with CONTAINS operator.
     *
     * Simplified representation:
     *
     *     value CONTAINS Two
     *
     * The result should contain Content Two.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindContainsTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new Field('data', Operator::CONTAINS, $valueTwo);

        $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with CONTAINS operator.
     *
     * Simplified representation:
     *
     *     NOT( value CONTAINS Two )
     *
     * The result should contain Content One.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotContainsTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        $criteria = new LogicalNot(
            new Field('data', Operator::CONTAINS, $valueTwo)
        );

        $this->assertFindResult($context, $criteria, true, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with LIKE operator, with NO wildcard.
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindLikeOne($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        // (in case test is skipped for current search engine)
        $this->supportsLikeWildcard($valueOne);

        $criteria = new Field('data', Operator::LIKE, $valueOne);

        $this->assertFindResult($context, $criteria, true, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with LIKE operator, with wildcard at the end (on strings).
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotLikeOne($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        if ($this->supportsLikeWildcard($valueOne)) {
            $valueOne = substr_replace($valueOne, '*', -1, 1);
        }

        $criteria = new LogicalNot(
            new Field('data', Operator::LIKE, $valueOne)
        );

        $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with LIKE operator, with wildcard at the start (on strings).
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindLikeTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        if ($this->supportsLikeWildcard($valueTwo)) {
            $valueTwo = substr_replace($valueTwo, '*', 1, 1);
        }

        $criteria = new Field('data', Operator::LIKE, $valueTwo);

        $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);

        // BC support for "%" for Legacy Storage engine only
        // @deprecated In 6.13.x/7.3.x and higher, to be removed in 8.0
        if (!$this->supportsLikeWildcard($valueTwo) || get_class($this->getSetupFactory()) !== Legacy::class) {
            return;
        }

        $criteria = new Field('data', Operator::LIKE, substr_replace($valueTwo, '%', 1, 1));

        $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with LIKE operator, with wildcard in the middle (on strings).
     *
     * @dataProvider findProvider
     * @depends testCreateTestContent
     */
    public function testFindNotLikeTwo($valueOne, $valueTwo, $filter, $content, $modifyField, array $context)
    {
        if ($this->supportsLikeWildcard($valueTwo)) {
            $valueTwo = substr_replace($valueTwo, '*', 2, 1);
        }

        $criteria = new LogicalNot(
            new Field('data', Operator::LIKE, $valueTwo)
        );

        $this->assertFindResult($context, $criteria, true, false, $filter, $content, $modifyField);
    }

    /**
     * Sets given custom field $fieldName on a Field criteria.
     *
     * $fieldName refers to additional field (to the default field) defined in Indexable definition,
     * and is resolved using FieldNameResolver.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $fieldName
     */
    protected function modifyFieldCriterion(Criterion $criterion, $fieldName)
    {
        $setupFactory = $this->getSetupFactory();
        /** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */
        $container = $setupFactory->getServiceContainer()->getInnerContainer();

        /** @var \eZ\Publish\Core\Search\Common\FieldNameResolver $fieldNameResolver */
        $fieldNameResolver = $container->get('ezpublish.search.common.field_name_resolver');
        $resolvedFieldNames = $fieldNameResolver->getFieldNames(
            $criterion,
            'data',
            $this->getTypeName(),
            $fieldName
        );
        $resolvedFieldName = reset($resolvedFieldNames);
        $criterion = [$criterion];

        $this->doModifyField($criterion, $resolvedFieldName);
    }

    /**
     * Sets given custom field $fieldName on a Field sort clause.
     *
     * $fieldName refers to additional field (to the default field) defined in Indexable definition,
     * and is resolved using FieldNameResolver.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param string $fieldName
     */
    protected function modifyFieldSortClause(SortClause $sortClause, $fieldName)
    {
        $setupFactory = $this->getSetupFactory();
        /** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */
        $container = $setupFactory->getServiceContainer()->getInnerContainer();

        /** @var \eZ\Publish\Core\Search\Common\FieldNameResolver $fieldNameResolver */
        $fieldNameResolver = $container->get('ezpublish.search.common.field_name_resolver');
        $resolvedFieldName = $fieldNameResolver->getSortFieldName(
            $sortClause,
            'test-' . $this->getTypeName(),
            'data',
            $fieldName
        );
        $sortClause = [$sortClause];

        $this->doModifyField($sortClause, $resolvedFieldName);
    }

    /**
     * Sets given custom field $fieldName on a Field criteria or sort clauses.
     *
     * Implemented separately to utilize recursion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion[]|\eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $criteriaOrSortClauses
     * @param string $fieldName
     */
    protected function doModifyField(array $criteriaOrSortClauses, $fieldName)
    {
        foreach ($criteriaOrSortClauses as $criterionOrSortClause) {
            if ($criterionOrSortClause instanceof LogicalOperator) {
                $this->doModifyField($criterionOrSortClause->criteria, $fieldName);
            } elseif ($criterionOrSortClause instanceof CustomFieldInterface) {
                $criterionOrSortClause->setCustomField(
                    'test-' . $this->getTypeName(),
                    'data',
                    $fieldName
                );
            }
        }
    }

    public function sortProvider()
    {
        $additionalFields = $this->getAdditionallyIndexedFieldData();
        $additionalFields[] = null;
        $templates = [
            [true, true],
            [true, false],
            [false, true],
            [false, false],
        ];

        $fixture = [];

        foreach ($additionalFields as $additionalField) {
            foreach ($templates as $template) {
                $template[] = $additionalField[0];
                $fixture[] = $template;
            }
        }

        return $fixture;
    }

    /**
     * Tests Content Search sort with Field sort clause on a field of specific field type.
     *
     * @dataProvider sortProvider
     * @depends testCreateTestContent
     */
    public function testSort($ascending, $content, $modifyField, array $context)
    {
        list($repository, $contentOneId, $contentTwoId) = $context;
        $sortClause = new FieldSortClause(
            'test-' . $this->getTypeName(),
            'data',
            $ascending ? Query::SORT_ASC : Query::SORT_DESC
        );

        if ($content) {
            $searchResult = $this->sortContent($repository, $sortClause);
        } else {
            $searchResult = $this->sortLocations($repository, $sortClause);
        }

        if ($modifyField !== null) {
            $this->checkCustomFieldsSupport();
            $this->modifyFieldSortClause($sortClause, $modifyField);
        }

        $this->assertSortResult($searchResult, $ascending, $contentOneId, $contentTwoId);
    }

    public function fullTextFindProvider()
    {
        $templates = [
            [true, true],
            [true, false],
            [false, true],
            [false, false],
        ];

        $fixture = [];

        foreach ($this->getFullTextIndexedFieldData() as $valueSet) {
            foreach ($templates as $template) {
                array_unshift($template, $valueSet[1]);
                array_unshift($template, $valueSet[0]);

                $fixture[] = $template;
            }
        }

        return $fixture;
    }

    /**
     * @dataProvider fullTextFindProvider
     * @depends testCreateTestContent
     */
    public function testFullTextFindOne($valueOne, $valueTwo, $filter, $content, array $context)
    {
        $this->checkFullTextSupport();

        $criteria = new Criterion\FullText($valueOne);

        $this->assertFindResult($context, $criteria, true, false, $filter, $content, null);
    }

    /**
     * @dataProvider fullTextFindProvider
     * @depends testCreateTestContent
     */
    public function testFullTextFindTwo($valueOne, $valueTwo, $filter, $content, array $context)
    {
        $this->checkFullTextSupport();

        $criteria = new Criterion\FullText($valueTwo);

        $this->assertFindResult($context, $criteria, false, true, $filter, $content, null);
    }

    /**
     * Returns SearchResult of the tested Content for the given $criterion.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param bool $filter Denotes search by filtering if true, search by querying if false
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    protected function findContent(Repository $repository, Criterion $criterion, $filter)
    {
        $searchService = $repository->getSearchService();

        if ($filter) {
            $criteriaProperty = 'filter';
        } else {
            $criteriaProperty = 'query';
        }

        $query = new Query(
            [
                $criteriaProperty => new Criterion\LogicalAnd(
                    [
                        new Criterion\ContentTypeIdentifier('test-' . $this->getTypeName()),
                        $criterion,
                    ]
                ),
            ]
        );

        return $searchService->findContent($query);
    }

    /**
     * Returns SearchResult of the tested Content for the given $sortClause.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    protected function sortContent(Repository $repository, SortClause $sortClause)
    {
        $searchService = $repository->getSearchService();

        $query = new Query(
            [
                'filter' => new Criterion\ContentTypeIdentifier('test-' . $this->getTypeName()),
                'sortClauses' => [
                    $sortClause,
                ],
            ]
        );

        return $searchService->findContent($query);
    }

    /**
     * Returns SearchResult of the tested Locations for the given $criterion.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param bool $filter Denotes search by filtering if true, search by querying if false
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    protected function findLocations(Repository $repository, Criterion $criterion, $filter)
    {
        $this->checkLocationFieldSearchSupport();
        $searchService = $repository->getSearchService();

        if ($filter) {
            $criteriaProperty = 'filter';
        } else {
            $criteriaProperty = 'query';
        }

        $query = new LocationQuery(
            [
                $criteriaProperty => new Criterion\LogicalAnd(
                    [
                        new Criterion\ContentTypeIdentifier('test-' . $this->getTypeName()),
                        $criterion,
                    ]
                ),
            ]
        );

        return $searchService->findLocations($query);
    }

    /**
     * Returns SearchResult of the tested Locations for the given $sortClause.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    protected function sortLocations(Repository $repository, SortClause $sortClause)
    {
        $this->checkLocationFieldSearchSupport();
        $searchService = $repository->getSearchService();

        $query = new LocationQuery(
            [
                'filter' => new Criterion\ContentTypeIdentifier('test-' . $this->getTypeName()),
                'sortClauses' => [
                    $sortClause,
                ],
            ]
        );

        return $searchService->findLocations($query);
    }

    /**
     * Returns a list of Content IDs from given $searchResult, with order preserved.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     *
     * @return array
     */
    protected function getResultContentIdList(SearchResult $searchResult)
    {
        $contentIdList = [];

        foreach ($searchResult->searchHits as $searchHit) {
            $valueObject = $searchHit->valueObject;

            switch (true) {
                case $valueObject instanceof Content:
                    $contentIdList[] = $valueObject->id;
                    break;

                case $valueObject instanceof Location:
                    $contentIdList[] = $valueObject->contentId;
                    break;

                default:
                    throw new \RuntimeException(
                        'Unknown search result hit type: ' . get_class($searchHit->valueObject)
                    );
            }
        }

        return $contentIdList;
    }

    /**
     * Asserts expected result, deliberately ignoring order.
     *
     * Search result can be empty, contain both Content One and Content Two or only one of them.
     *
     * @param array $context
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param bool $includesOne
     * @param bool $includesTwo
     * @param bool $filter
     * @param bool $content
     * @param null|string $modifyField
     */
    protected function assertFindResult(
        array $context,
        Criterion $criterion,
        $includesOne,
        $includesTwo,
        $filter,
        $content,
        $modifyField
    ) {
        list($repository, $contentOneId, $contentTwoId) = $context;

        if ($modifyField !== null) {
            $this->checkCustomFieldsSupport();
            $this->modifyFieldCriterion($criterion, $modifyField);
        }

        if ($content) {
            $searchResult = $this->findContent($repository, $criterion, $filter);
        } else {
            $searchResult = $this->findLocations($repository, $criterion, $filter);
        }

        $contentIdList = $this->getResultContentIdList($searchResult);

        if ($includesOne && $includesTwo) {
            $this->assertEquals(2, $searchResult->totalCount);
            $this->assertNotEquals($contentIdList[0], $contentIdList[1]);

            $this->assertThat(
                $contentIdList[0],
                $this->logicalOr($this->equalTo($contentOneId), $this->equalTo($contentTwoId))
            );

            $this->assertThat(
                $contentIdList[1],
                $this->logicalOr($this->equalTo($contentOneId), $this->equalTo($contentTwoId))
            );
        } elseif (!$includesOne && !$includesTwo) {
            $this->assertEquals(0, $searchResult->totalCount);
        } else {
            $this->assertEquals(1, $searchResult->totalCount);

            if ($includesOne) {
                $this->assertEquals($contentOneId, $contentIdList[0]);
            }

            if ($includesTwo) {
                $this->assertEquals($contentTwoId, $contentIdList[0]);
            }
        }
    }

    /**
     * Asserts order of the given $searchResult, both Content One and Two are always expected.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     * @param bool $ascending Denotes ascending order if true, descending order if false
     * @param string|int $contentOneId
     * @param string|int $contentTwoId
     */
    protected function assertSortResult(
        SearchResult $searchResult,
        $ascending,
        $contentOneId,
        $contentTwoId
    ) {
        $contentIdList = $this->getResultContentIdList($searchResult);

        $indexOne = 0;
        $indexTwo = 1;

        if (!$ascending) {
            $indexOne = 1;
            $indexTwo = 0;
        }

        $this->assertEquals(2, $searchResult->totalCount);
        $this->assertEquals($contentOneId, $contentIdList[$indexOne]);
        $this->assertEquals($contentTwoId, $contentIdList[$indexTwo]);
    }
}
