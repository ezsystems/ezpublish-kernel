<?php

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser\FacetBuilder;

use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Server\Input\Parser\ContentQuery as QueryParser;
use eZ\Publish\Core\REST\Server\Input\Parser\FacetBuilder\ContentTypeParser;
use eZ\Publish\Core\REST\Server\Input\Parser\FacetBuilder\CriterionParser;
use eZ\Publish\Core\REST\Server\Input\Parser\FacetBuilder\DateRangeParser;
use eZ\Publish\Core\REST\Server\Input\Parser\FacetBuilder\FieldParser;
use eZ\Publish\Core\REST\Server\Input\Parser\FacetBuilder\FieldRangeParser;
use eZ\Publish\Core\REST\Server\Input\Parser\FacetBuilder\LocationParser;
use eZ\Publish\Core\REST\Server\Input\Parser\FacetBuilder\SectionParser;
use eZ\Publish\Core\REST\Server\Input\Parser\FacetBuilder\TermParser;
use eZ\Publish\Core\REST\Server\Input\Parser\FacetBuilder\UserParser;
use eZ\Publish\Core\REST\Server\Tests\Input\Parser\BaseTest;

abstract class FacetBuilderBaseTest extends BaseTest
{
    /**
     * @return \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher
     */
    protected function getParsingDispatcher()
    {
        $parsingDispatcher = new ParsingDispatcher();

        $parsingDispatcher->addParser(
            'application/vnd.ez.api.internal.facetbuilder.ContentType',
            new ContentTypeParser()
        );

        $parsingDispatcher->addParser(
            'application/vnd.ez.api.internal.facetbuilder.Criterion',
            new CriterionParser()
        );

        $parsingDispatcher->addParser(
            'application/vnd.ez.api.internal.facetbuilder.DateRange',
            new DateRangeParser()
        );

        $parsingDispatcher->addParser(
            'application/vnd.ez.api.internal.facetbuilder.Field',
            new FieldParser()
        );

        $parsingDispatcher->addParser(
            'application/vnd.ez.api.internal.facetbuilder.FieldRange',
            new FieldRangeParser()
        );

        $parsingDispatcher->addParser(
            'application/vnd.ez.api.internal.facetbuilder.Location',
            new LocationParser()
        );

        $parsingDispatcher->addParser(
            'application/vnd.ez.api.internal.facetbuilder.Section',
            new SectionParser()
        );

        $parsingDispatcher->addParser(
            'application/vnd.ez.api.internal.facetbuilder.Term',
            new TermParser()
        );

        $parsingDispatcher->addParser(
            'application/vnd.ez.api.internal.facetbuilder.User',
            new UserParser()
        );

        return $parsingDispatcher;
    }

    /**
     * Returns the query parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\ContentQuery
     */
    protected function internalGetParser()
    {
        return new QueryParser();
    }
}
