<?php
/**
 * FindContentSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\SearchService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * FindContentSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\SearchService
 */
class FindContentSignal extends Signal
{
    /**
     * Query
     *
     * @var eZ\Publish\API\Repository\Values\Content\Query
     */
    public $query;

    /**
     * FieldFilters
     *
     * @var mixed
     */
    public $fieldFilters;

    /**
     * FilterOnUserPermissions
     *
     * @var mixed
     */
    public $filterOnUserPermissions;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\Content\Query $query
     * @param mixed $fieldFilters
     * @param mixed $filterOnUserPermissions
     */
    public function __construct( $query, $fieldFilters, $filterOnUserPermissions )
    {
        $this->query = $query;
        $this->fieldFilters = $fieldFilters;
        $this->filterOnUserPermissions = $filterOnUserPermissions;
    }
}

