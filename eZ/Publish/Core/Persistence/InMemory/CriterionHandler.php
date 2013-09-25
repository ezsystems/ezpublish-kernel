<?php
/**
 * File containing the InMemory criterion handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

abstract class CriterionHandler
{
    /**
     * @var \eZ\Publish\Core\Persistence\InMemory\LocationHandler
     */
    protected $locationHandler;

    /**
     * @var \eZ\Publish\Core\Persistence\InMemory\Backend
     */
    protected $backend;

    /**
     * Creates a new criterion handler
     *
     * @param \eZ\Publish\Core\Persistence\InMemory\LocationHandler $locationHandler
     * @param \eZ\Publish\Core\Persistence\InMemory\Backend $backend
     */
    public function __construct( LocationHandler $locationHandler, Backend $backend )
    {
        $this->locationHandler = $locationHandler;
        $this->backend = $backend;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return boolean
     */
    abstract public function accept( Criterion $criterion );

    /**
     * Generate query expression for a Criterion this handler accepts
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $match
     * @param array $excludeMatch
     */
    abstract public function handle( Criterion $criterion, array &$match, array &$excludeMatch );
}
