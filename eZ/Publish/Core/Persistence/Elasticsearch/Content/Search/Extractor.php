<?php
/**
 * File containing the Search Loader Extractor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search;

use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;

/**
 * The Search Extractor extracts the search result from the data returned
 * from Elasticsearch index.
 */
abstract class Extractor
{
    /**
     *
     *
     * @param mixed $data
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function extract( $data )
    {
        $result = new SearchResult(
            array(
                "time" => $data->took,
                "maxScore" => $data->hits->max_score,
                "totalCount" => $data->hits->total,
            )
        );

        foreach ( $data->hits->hits as $hit )
        {
            $searchHit = new SearchHit(
                array(
                    "score" => $hit->_score,
                    "valueObject" => $this->extractHit( $hit )
                )
            );
            $result->searchHits[] = $searchHit;
        }

        return $result;
    }

    /**
     *
     *
     * @param mixed $hit
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    abstract public function extractHit( $hit );
}
