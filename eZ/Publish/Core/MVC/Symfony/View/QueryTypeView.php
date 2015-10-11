<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;

class QueryTypeView extends BaseView implements View
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Query
     */
    private $query;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\SearchResult[]
     */
    private $searchResult;

    /**
     * @var string
     */
    private $queryTypeName;

    /**
     * @var array
     */
    private $queryParameters = [];

    /**
     * The value object type that was searched for (content, location or contentInfo).
     * @var string
     */
    private $searchedType;

    const SEARCH_TYPE_CONTENT = 'content';
    const SEARCH_TYPE_LOCATION = 'location';
    const SEARCH_TYPE_CONTENT_INFO = 'contentInfo';

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function getSearchResult()
    {
        return $this->searchResult;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult|\Pagerfanta\Pagerfanta $searchResults
     */
    public function setSearchResult($searchResults)
    {
        $this->searchResult = $searchResults;
    }

    /**
     * @return mixed
     */
    public function getQueryTypeName()
    {
        return $this->queryTypeName;
    }

    /**
     * @param mixed $queryTypeName
     */
    public function setQueryTypeName($queryTypeName)
    {
        $this->queryTypeName = $queryTypeName;
    }

    /**
     * @return array
     */
    public function getQueryParameters()
    {
        return $this->queryParameters;
    }

    /**
     * @param mixed $queryParameters
     */
    public function setQueryParameters(array $queryParameters)
    {
        $this->queryParameters = $queryParameters;
    }

    /**
     * @return string
     */
    public function getSearchedType()
    {
        return $this->searchedType;
    }

    /**
     * @param string $searchedType
     */
    public function setSearchedType($searchedType)
    {
        $this->searchedType = $searchedType;
    }
}
