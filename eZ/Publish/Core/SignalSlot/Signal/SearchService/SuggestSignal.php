<?php
/**
 * SuggestSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\SearchService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * SuggestSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\SearchService
 */
class SuggestSignal extends Signal
{
    /**
     * Prefix
     *
     * @var mixed
     */
    public $prefix;

    /**
     * FieldPaths
     *
     * @var mixed
     */
    public $fieldPaths;

    /**
     * Limit
     *
     * @var mixed
     */
    public $limit;

    /**
     * Filter
     *
     * @var eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public $filter;

}

