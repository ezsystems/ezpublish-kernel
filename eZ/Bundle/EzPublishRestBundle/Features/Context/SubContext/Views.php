<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext;

use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\Core\REST\Client\Values\View;
use PHPUnit_Framework_Assert as Assertion;

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
}
