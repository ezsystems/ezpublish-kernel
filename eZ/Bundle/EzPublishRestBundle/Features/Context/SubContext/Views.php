<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\Core\REST\Client\Values\View;
use PHPUnit\Framework\Assert as Assertion;

/**
 * @method mixed getResponseObject
 */
trait Views
{
    /**
     * @Given /^the View contains Search Hits$/
     */
    public function theViewContainsSearchHits()
    {
        /** @var View $view */
        $view = $this->getResponseObject();
        Assertion::assertGreaterThan(1, count($view->result->searchHits));
    }

    /**
     * @Given /^the Search Hits are Content objects$/
     */
    public function theSearchHitsAreContentObjects()
    {
        /** @var SearchHit[] $searchHits */
        $searchHits = $this->getResponseObject()->result->searchHits;
        foreach ($searchHits as $searchHit) {
            Assertion::assertInstanceOf('eZ\Publish\API\Repository\Values\Content\Content', $searchHit->valueObject);
        }
    }

    /**
     * @Given /^I set field "([^"]*)" to a Query object$/
     */
    public function iSetFieldToAQueryObject($field)
    {
        $this->requestObject->$field = new Query();
    }

    /**
     * @Given /^I set the "([^"]*)" property of the Query to a valid Criterion$/
     */
    public function iSetTheFilterPropertyOfTheQuery($field)
    {
        // @todo this could be improved if setFieldToValue used PropertyAccessor.
        $this->requestObject->contentQuery->$field = new Criterion\ContentTypeIdentifier('folder');
    }
}
