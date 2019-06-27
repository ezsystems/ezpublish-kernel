<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\FieldType;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot;

/**
 * Integration test for searching and sorting with Field criterion and Field sort clause.
 *
 * This test is based on SearchBaseIntegrationTest, expanding it with analogous methods to
 * provide multivalued field data. Methods provided in SearchBaseIntegrationTest are documented
 * there.
 *
 * To get the test working extend it in a concrete field type test and implement:
 *
 * - getValidMultivaluedSearchValueOne()
 * - getValidMultivaluedSearchValueTwo()
 *
 * If needed you can override the methods that provide Field criterion target value and
 * which by default fall back to the methods mentioned above:
 *
 * - getMultivaluedSearchTargetValueOne()
 * - getMultivaluedSearchTargetValueTwo()
 *
 * In order to test fields additionally indexed by the field type, provide the required
 * data by overriding method:
 *
 * - getAdditionallyIndexedMultivaluedFieldData()
 *
 * Value should contain at least two distinct values, appearing in ascending order.
 */
abstract class SearchMultivaluedBaseIntegrationTest extends SearchBaseIntegrationTest
{
    /**
     * Get search field multivalued value One.
     *
     * The value must be valid for Content creation.
     *
     * Value should contain at least two distinct values, appearing in ascending order.
     * Additionally, there should be no overlapping with values provided through
     * {@link self::getValidMultivaluedSearchValuesTwo()}
     *
     * @return mixed
     */
    abstract protected function getValidMultivaluedSearchValuesOne();

    /**
     * Get search target field value One.
     *
     * Returns the Field criterion target value for the field value One.
     * Default implementation falls back on {@link getValidSearchValueOne}.
     *
     * @return mixed
     */
    protected function getMultivaluedSearchTargetValuesOne()
    {
        return $this->getValidMultivaluedSearchValuesOne();
    }

    /**
     * Get search field multivalued value Two.
     *
     * The value must be valid for Content creation.
     *
     * Value should contain at least two distinct values, appearing in ascending order.
     * Additionally, there should be no overlapping with values provided through
     * {@link self::getValidMultivaluedSearchValuesOne()}
     *
     * @return mixed
     */
    abstract protected function getValidMultivaluedSearchValuesTwo();

    /**
     * Get search target field value Two.
     *
     * Returns the Field criterion target value for the field value Two.
     * Default implementation falls back on {@link getValidSearchValueTwo}.
     *
     * @return mixed
     */
    protected function getMultivaluedSearchTargetValuesTwo()
    {
        return $this->getValidMultivaluedSearchValuesTwo();
    }

    protected function getAdditionallyIndexedMultivaluedFieldData()
    {
        return [];
    }

    /** @var array Overload per FieldType on what is supported. */
    protected $legacyUnsupportedOperators = [
        Operator::EQ => 'EQ',
        Operator::IN => 'IN',
        Operator::GT => 'GT',
        Operator::GTE => 'GTE',
        Operator::LT => 'LT',
        Operator::LTE => 'LTE',
        Operator::BETWEEN => 'BETWEEN',
    ];

    protected function checkOperatorSupport($operator)
    {
        if (ltrim(get_class($this->getSetupFactory()), '\\') === 'eZ\\Publish\\API\\Repository\\Tests\\SetupFactory\\Legacy') {
            if (isset($this->legacyUnsupportedOperators[$operator])) {
                $this->markTestSkipped(
                    'Legacy Search Engine does not properly support multivalued fields ' .
                    "with '{$this->legacyUnsupportedOperators[$operator]}' operator"
                );
            }
        }
    }

    /**
     * Proxy method for creating test Content Type.
     *
     * Defaults to the testCreateContentType() in the base field type test,
     * override in the concrete test as needed.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function createTestContentType()
    {
        return $this->testCreateContentType();
    }

    /**
     * Creates test Content and Locations and returns the context for subsequent testing.
     *
     * Context consists of repository instance and created Content IDs.
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function testCreateMultivaluedTestContent()
    {
        $repository = $this->getRepository();
        $fieldTypeService = $repository->getFieldTypeService();
        $fieldType = $fieldTypeService->getFieldType($this->getTypeName());

        if (!$fieldType->isSearchable()) {
            $this->markTestSkipped("Field type '{$this->getTypeName()}' is not searchable.");
        }

        $this->checkSearchEngineSupport();

        $contentType = $this->createTestContentType();

        $context = [
            $repository,
            $this->createTestSearchContent(
                $this->getValidMultivaluedSearchValuesOne(),
                $repository,
                $contentType
            )->id,
            $this->createTestSearchContent(
                $this->getValidMultivaluedSearchValuesTwo(),
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
    public function findMultivaluedProvider()
    {
        $additionalFields = $this->getAdditionallyIndexedMultivaluedFieldData();
        $additionalFields[] = [
            null,
            $this->getMultivaluedSearchTargetValuesOne(),
            $this->getMultivaluedSearchTargetValuesTwo(),
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
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedEqualsOne($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::EQ);

        foreach ($valuesOne as $value) {
            $criteria = new Field('data', Operator::EQ, $value);

            $this->assertFindResult($context, $criteria, true, false, $filter, $content, $modifyField);
        }
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
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedNotEqualsOne($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::EQ);

        foreach ($valuesOne as $value) {
            $criteria = new LogicalNot(new Field('data', Operator::EQ, $value));

            $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);
        }
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
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedInOne($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::IN);

        $criteria = new Field('data', Operator::IN, $valuesOne);

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
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedNotInOne($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::IN);

        $criteria = new LogicalNot(
            new Field('data', Operator::IN, $valuesOne)
        );

        $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);
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
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedInOneTwo($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::IN);

        $criteria = new Field('data', Operator::IN, array_merge($valuesOne, $valuesTwo));

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
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedNotInOneTwo($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::IN);

        $criteria = new LogicalNot(
            new Field('data', Operator::IN, array_merge($valuesOne, $valuesTwo))
        );

        $this->assertFindResult($context, $criteria, false, false, $filter, $content, $modifyField);
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
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedContainsOne($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::CONTAINS);

        foreach ($valuesOne as $value) {
            $criteria = new Field('data', Operator::CONTAINS, $value);

            $this->assertFindResult($context, $criteria, true, false, $filter, $content, $modifyField);
        }
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
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedNotContainsOne($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::CONTAINS);

        foreach ($valuesOne as $value) {
            $criteria = new LogicalNot(new Field('data', Operator::CONTAINS, $value));

            $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);
        }
    }

    /**
     * Tests search with GT operator.
     *
     * Simplified representation:
     *
     *     value GT One[0]
     *
     * The result should contain both Content One and Content Two.
     *
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedGreaterThanOneFindsOneTwo($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::GT);

        $criteria = new Field('data', Operator::GT, reset($valuesOne));

        $this->assertFindResult($context, $criteria, true, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with GT operator.
     *
     * Simplified representation:
     *
     *     value GT One[1]
     *
     * The result should contain Content Two.
     *
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedGreaterThanOneFindsTwo($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::GT);

        $criteria = new Field('data', Operator::GT, end($valuesOne));

        $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with GT operator.
     *
     * Simplified representation:
     *
     *     NOT( value GT One[0] )
     *
     * The result should be empty.
     *
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedNotGreaterThanOneFindsOneTwo($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::GT);

        $criteria = new LogicalNot(new Field('data', Operator::GT, reset($valuesOne)));

        $this->assertFindResult($context, $criteria, false, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with GT operator.
     *
     * Simplified representation:
     *
     *     NOT( value GT One[1] )
     *
     * The result should contain Content One.
     *
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedNotGreaterThanOneFindsTwo($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::GT);

        $criteria = new LogicalNot(new Field('data', Operator::GT, end($valuesOne)));

        $this->assertFindResult($context, $criteria, true, false, $filter, $content, $modifyField);
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
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedGreaterThanOrEqualOne($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::GTE);

        foreach ($valuesOne as $value) {
            $criteria = new Field('data', Operator::GTE, $value);

            $this->assertFindResult($context, $criteria, true, true, $filter, $content, $modifyField);
        }
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
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedNotGreaterThanOrEqual($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::GTE);

        foreach ($valuesOne as $value) {
            $criteria = new LogicalNot(new Field('data', Operator::GTE, $value));

            $this->assertFindResult($context, $criteria, false, false, $filter, $content, $modifyField);
        }
    }

    /**
     * Tests search with LT operator.
     *
     * Simplified representation:
     *
     *     value LT One[0]
     *
     * The result should be empty.
     *
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedLowerThanOneEmpty($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::LT);

        $criteria = new Field('data', Operator::LT, reset($valuesOne));

        $this->assertFindResult($context, $criteria, false, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with LT operator.
     *
     * Simplified representation:
     *
     *     value LT One[1]
     *
     * The result should contain Content One.
     *
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedLowerThanOneFindsOne($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::LT);

        $criteria = new Field('data', Operator::LT, end($valuesOne));

        $this->assertFindResult($context, $criteria, true, false, $filter, $content, $modifyField);
    }

    /**
     * Tests search with LT operator.
     *
     * Simplified representation:
     *
     *     NOT( value LT One[0] )
     *
     * The result should contain both Content One and Content Two.
     *
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedNotLowerThanOneEmpty($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::LT);

        $criteria = new LogicalNot(new Field('data', Operator::LT, reset($valuesOne)));

        $this->assertFindResult($context, $criteria, true, true, $filter, $content, $modifyField);
    }

    /**
     * Tests search with LT operator.
     *
     * Simplified representation:
     *
     *     NOT( value LT One[1] )
     *
     * The result should contain Content Two.
     *
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedNotLowerThanOneFindsOne($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::LT);

        $criteria = new LogicalNot(new Field('data', Operator::LT, end($valuesOne)));

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
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedLowerThanOrEqualOne($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::LTE);

        foreach ($valuesOne as $value) {
            $criteria = new Field('data', Operator::LTE, $value);

            $this->assertFindResult($context, $criteria, true, false, $filter, $content, $modifyField);
        }
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
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedNotLowerThanOrEqualOne($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::LTE);

        foreach ($valuesOne as $value) {
            $criteria = new LogicalNot(new Field('data', Operator::LTE, $value));

            $this->assertFindResult($context, $criteria, false, true, $filter, $content, $modifyField);
        }
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
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedBetweenOneTwo($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::BETWEEN);

        foreach ($valuesOne as $valueOne) {
            foreach ($valuesTwo as $valueTwo) {
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
        }
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
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedNotBetweenOneTwo($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::BETWEEN);

        foreach ($valuesOne as $valueOne) {
            foreach ($valuesTwo as $valueTwo) {
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
        }
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
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedBetweenTwoOne($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::BETWEEN);

        foreach ($valuesOne as $valueOne) {
            foreach ($valuesTwo as $valueTwo) {
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
        }
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
     * @dataProvider findMultivaluedProvider
     * @depends testCreateMultivaluedTestContent
     */
    public function testFindMultivaluedNotBetweenTwoOne($valuesOne, $valuesTwo, $filter, $content, $modifyField, array $context)
    {
        $this->checkOperatorSupport(Operator::BETWEEN);

        foreach ($valuesOne as $valueOne) {
            foreach ($valuesTwo as $valueTwo) {
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
        }
    }
}
