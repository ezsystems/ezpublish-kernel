<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Search;

use ezp\Persistence\Content,
    ezp\Persistence\Content\Search\Handler as BaseSearchHandler,
    ezp\Persistence\Content\Search\Result,
    ezp\Persistence\Content\Criterion,
    ezp\Persistence\Storage\Legacy\Exception,
    ezp\Persistence\Storage\Legacy\Content\Mapper as ContentMapper;

/**
 * The Content Search handler retrieves sets of of Content objects, based on a
 * set of criteria.
 *
 * The basic idea of this class is to do the following:
 *
 * 1) The find methods retrieve a recursive set of filters, which define which
 * content objects to retrieve from the database. Those may be combined using
 * boolean opeartors.
 *
 * 2) This recursive criterion definition is visited into a query, which limits
 * the content retrieved from the database. We might not be able to create
 * sensible queries from all criterion definitions.
 *
 * 3) The query might be possible to optimize (remove empty statements),
 * reduce singular and and or constructs…
 *
 * 4) Additionally we might need a post-query filtering step, which filters
 * content objects based on criteria, which could not be convertedd in to
 * database statements.
 */
class Handler extends BaseSearchHandler
{
    /**
     * Content locator gateway.
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Search\Gateway
     */
    protected $gateway;

    /**
     * Content mapper
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Mapper
     */
    protected $contentMapper;

    /**
     * Creates a new content handler.
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Search\Gateway $gateway
     * @param \ezp\Persistence\Storage\Legacy\Content\Mapper
     */
    public function __construct( Gateway $gateway, ContentMapper $contentMapper )
    {
        $this->gateway = $gateway;
        $this->contentMapper = $contentMapper;
    }

    /**
     * Returns a list of object satisfying the $criterion.
     *
     * Optionally a translation filter may be specified. If specified only the
     * translations with the listed language codes will be retrieved. If not,
     * all translations will be retrieved.
     *
     * @param \ezp\Persistence\Content\Criterion $criterion
     * @param int $offset
     * @param int|null $limit
     * @param \ezp\Persistence\Content\Query\SortClause[] $sort
     * @param string[] $translations
     * @return ezp\Persistence\Content\Search\Result
     */
    public function find( Criterion $criterion, $offset = 0, $limit = null, array $sort = null, $translations = null )
    {
        $data = $this->gateway->find( $criterion, $offset, $limit, $sort );

        $result = new Result();
        $result->count = $data['count'];
        $result->content = $this->contentMapper->extractContentFromRows(
            $data['rows']
        );

        return $result;
    }

    /**
     * Returns a single Content object found.
     *
     * Performs a {@link find()} query to find a single object. You need to
     * ensure, that your $criterion ensure that only a single object can be
     * retrieved.
     *
     * Optionally a translation filter may be specified. If specified only the
     * translations with the listed language codes will be retrieved. If not,
     * all translations will be retrieved.
     *
     * @param Criterion $criterion
     * @param string[] $translations
     * @return \ezp\Persistence\Content
     */
    public function findSingle( Criterion $criterion, $translations = null )
    {
        $result = $this->find( $criterion, 0, 1, null, $translations );

        if ( $result->count !== 1 )
        {
            throw new Exception\InvalidObjectCount(
                'Expected exactly one object to be found -- found ' . $result->count . '.'
            );
        }

        return reset( $result->content );
    }

    /**
     * Indexes a content object
     *
     * @param ezp\Persistence\Content $content
     * @return void
     */
    public function indexContent( Content $content )
    {
        throw new \Exception( "Not implemented yet." );
    }
}

