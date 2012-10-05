<?php
/**
 * File containing the ContentObjectStates class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * ContentObjectStates view model
 */
class ContentObjectStates extends RestValue
{
    /**
     * Object states
     *
     * @var array
     */
    public $states;

    /**
     * Construct
     *
     * @param array $states
     */
    public function __construct( array $states )
    {
        $this->states = $states;
    }
}
