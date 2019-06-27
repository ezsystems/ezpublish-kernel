<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\Core\REST\Server\Controller;
use Symfony\Component\HttpFoundation\Request;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Server\Values;

/**
 * Controller for Repository Views (Search, mostly).
 */
class Views extends Controller
{
    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Creates and executes a content view.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestExecutedView
     */
    public function createView(Request $request)
    {
        $viewInput = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        if ($viewInput->query instanceof LocationQuery) {
            $method = 'findLocations';
        } else {
            $method = 'findContent';
        }

        return new Values\RestExecutedView(
            [
                'identifier' => $viewInput->identifier,
                'searchResults' => $this->searchService->$method(
                    $viewInput->query,
                    ['languages' => Language::ALL]
                ),
            ]
        );
    }

    /**
     * List content views.
     *
     * @return NotImplementedException;
     */
    public function listView()
    {
        return new NotImplementedException('ezpublish_rest.controller.content:listView');
    }

    /**
     * Get a content view.
     *
     * @return NotImplementedException;
     */
    public function getView()
    {
        return new NotImplementedException('ezpublish_rest.controller.content:getView');
    }

    /**
     * Get a content view results.
     *
     * @return NotImplementedException;
     */
    public function loadViewResults()
    {
        return new NotImplementedException('ezpublish_rest.controller.content:loadViewResults');
    }
}
