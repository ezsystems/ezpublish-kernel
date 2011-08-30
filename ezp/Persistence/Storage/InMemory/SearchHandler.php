<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\InMemory;

use ezp\Persistence\Content,
    ezp\Persistence\Content\Search\Handler,
    ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\Criterion\ContentId,
    ezp\Persistence\Content\Criterion\Operator,
    Exception;

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
class SearchHandler extends Handler
{
    /**
     * @var RepositoryHandler
     */
    protected $handler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to RepositoryHandler object that created it.
     *
     * @param RepositoryHandler $handler
     * @param Backend $backend The storage engine backend
     */
    public function __construct( RepositoryHandler $handler, Backend $backend )
    {
        $this->handler = $handler;
        $this->backend = $backend;
    }

    /**
     * Returns a list of object satisfying the $criterion.
     *
     * Optionally a translation filter may be specified. If specified only the
     * translations with the listed language codes will be retrieved. If not,
     * all translations will be retrieved.
     *
     * @todo Finish implementation
     * @param Criterion $criterion
     * @param int $offset
     * @param int|null $limit
     * @param $sort
     * @param string[] $translations
     * @return ezp\Persistence\Content\Search\Result
     */
    public function find( Criterion $criterion, $offset = 0, $limit = null, $sort = null, $translations = null )
    {
        throw new Exception( "Not implemented yet." );
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
     * @todo Finish implementation
     * @param Criterion $criterion
     * @param string[] $translations
     * @return \ezp\Persistence\Content
     */
    public function findSingle( Criterion $criterion, $translations = null )
    {
        // Using "Coding by exception" anti pattern since it has been decided
        // not to implement search functionalities in InMemoryEngine
        if ( $criterion instanceof ContentId && $criterion->operator === Operator::EQ )
        {
            $content = $this->backend->load( "Content", $criterion->value[0] );

            $versions = $this->backend->find( "Content\\Version", array( "contentId" => $content->id ) );
            $versions[0]->fields = $this->backend->find( "Content\\Field", array( "versionNo" => $versions[0]->id ) );

            $content->version = $versions[0];

            // @todo Loading locations by content object id should be possible using handler API.
            $content->locations = $this->backend->find( "Content\\Location", array( "contentId" => $content->id  ) );
            return $content;
        }

        throw new Exception( "Not implemented yet." );
    }

    /**
     * Indexes a content object
     *
     * @todo Finish implementation
     * @param ezp\Persistence\Content $content
     * @return void
     */
    public function indexContent( Content $content )
    {
        throw new Exception( "Not implemented yet." );
    }
}
