<?php

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser\FacetBuilder;

use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\ContentTypeFacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\CriterionFacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\FieldFacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\LocationFacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\SectionFacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\TermFacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\UserFacetBuilder;
use eZ\Publish\Core\REST\Server\Input\Parser\Criterion\LocationId;

class FacetBuilderParserTest extends FacetBuilderBaseTest
{
    public function testGenericFacetBuilder()
    {
        $inputArray = [
            'Filter' => [],
            'Criteria' => [],
            'Query' => [],
            'FacetBuilders' => [
                'ContentType' => [
                    'name' => 'Generic test',
                    'limit' => 5,
                    'minCount' => 3,
                    'global' => true,
                    'filter' => new LocationId(123),
                ],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new ContentTypeFacetBuilder([
                    'name' => 'Generic test',
                    'limit' => 5,
                    'minCount' => 3,
                    'global' => true,
                    'filter' => new LocationId(123),
                ]),
            ],
        ]);

        $this->assertEquals($expectedQuery, $result);
    }

    public function testQueryContentTypeFacetBuilder()
    {
        $inputArray = [
            'Filter' => [],
            'Criteria' => [],
            'Query' => [],
            'FacetBuilders' => [
                'ContentType' => [],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new ContentTypeFacetBuilder(),
            ],
        ]);

        $this->assertEquals($expectedQuery, $result);
    }

    public function testQueryCriterionFacetBuilder()
    {
        $inputArray = [
            'Filter' => [],
            'Criteria' => [],
            'Query' => [],
            'FacetBuilders' => [
                'Criterion' => [
                    'filter' => new LocationId(123),
                ],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new CriterionFacetBuilder([
                    'filter' => new LocationId(123),
                ]),
            ],
        ]);

        $this->assertEquals($expectedQuery, $result);
    }

    public function testQueryFieldFacetBuilder()
    {
        $inputArray = [
            'Filter' => [],
            'Criteria' => [],
            'Query' => [],
            'FacetBuilders' => [
                'Field' => [
                    'fieldPaths' => [
                        'description',
                    ],
                    'regex' => '/^Description starts with:.*/',
                    'sort' => 'COUNT_DESC',
                ],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new FieldFacetBuilder([
                    'fieldPaths' => [
                        'description',
                    ],
                    'regex' => '/^Description starts with:.*/',
                    'sort' => FieldFacetBuilder::COUNT_DESC,
                ]),
            ],
        ]);

        $this->assertEquals($expectedQuery, $result);
    }

    public function testQueryLocationFacetBuilder()
    {
        $inputArray = [
            'Filter' => [],
            'Criteria' => [],
            'Query' => [],
            'FacetBuilders' => [
                'Location' => [
                    'location' => new Location(),
                ],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new LocationFacetBuilder([
                    'location' => new Location(),
                ]),
            ],
        ]);

        $this->assertEquals($expectedQuery, $result);
    }

    public function testQuerySectionFacetBuilder()
    {
        $inputArray = [
            'Filter' => [],
            'Criteria' => [],
            'Query' => [],
            'FacetBuilders' => [
                'Section' => [],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new SectionFacetBuilder(),
            ],
        ]);

        $this->assertEquals($expectedQuery, $result);
    }

    public function testQueryTermFacetBuilder()
    {
        $inputArray = [
            'Filter' => [],
            'Criteria' => [],
            'Query' => [],
            'FacetBuilders' => [
                'Term' => [],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new TermFacetBuilder(),
            ],
        ]);

        $this->assertEquals($expectedQuery, $result);
    }

    public function testQueryUserFacetBuilder()
    {
        $inputArray = [
            'Filter' => [],
            'Criteria' => [],
            'Query' => [],
            'FacetBuilders' => [
                'User' => [
                    'select' => 'MODIFIER',
                ],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new UserFacetBuilder([
                    'type' => UserFacetBuilder::MODIFIER,
                ]),
            ],
        ]);

        $this->assertEquals($expectedQuery, $result);
    }

    public function testQueryNoFacetBuilders()
    {
        $inputArray = [
            'Filter' => [],
            'Criteria' => [],
            'Query' => [],
            'FacetBuilders' => [],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [],
        ]);

        $this->assertEquals($expectedQuery, $result);
    }

    public function testQueryMultipleFacetBuilders()
    {
        $inputArray = [
            'Filter' => [],
            'Criteria' => [],
            'Query' => [],
            'FacetBuilders' => [
                'ContentType' => [],
                'User' => [
                    'select' => 'MODIFIER',
                ],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new ContentTypeFacetBuilder(),
                new UserFacetBuilder([
                    'type' => UserFacetBuilder::MODIFIER,
                ]),
            ],
        ]);

        $this->assertEquals($expectedQuery, $result);
    }
}
