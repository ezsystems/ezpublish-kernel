<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Renderer;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\View\QueryTypeResult;
use eZ\Publish\Core\MVC\Symfony\View\ViewRenderer;

/**
 * Renders a QueryTypeResult
 */
class QueryTypeResultRenderer extends ViewProviderBased implements ViewRenderer
{
    /**
     * The type of Query rendered by this class. Ex:
     * @var string
     */
    private $renderedQueryType;

    /**
     * Name of the template variable used to assign the search results. Ex: "content_list"
     * @var string
     */
    private $templateVariableName;

    /**
     * Sets the type of API Query rendered by this class.
     * @param $renderedQueryType
     */
    public function setRenderedQueryType($renderedQueryType)
    {
        $this->renderedQueryType = $renderedQueryType;
    }

    /**
     * Sets the name of the template variable used to assign the search results. Ex: "content_list"
     *
     * @param $templateVariableName
     */
    public function setTemplateVariableName($templateVariableName)
    {
        $this->templateVariableName = $templateVariableName;
    }

    /**
     * Tests if the ViewRenderer can render $value.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $value
     *
     * @return bool true if the ViewRenderer can render $value
     */
    public function canRender(ValueObject $value)
    {
        return ($value instanceof QueryTypeResult) && ($value->query instanceof $this->renderedQueryType);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ValueObject $queryTypeResult
     * @param array $params
     * @return void
     */
    protected function filterRenderingParameters(ValueObject $queryTypeResult, array &$params)
    {
        $params += [
            $this->templateVariableName => $queryTypeResult->searchResult->searchHits,
            'list_count' => $queryTypeResult->searchResult->totalCount,
            'parameters' => $queryTypeResult->parameters
        ];
    }
}
