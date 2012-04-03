<?php
/**
 * File containing the SectionList class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Values;

class SectionList
{
    /**
     * Sections
     *
     * @var array
     */
    public $sections;

    /**
     * Csonstruct
     *
     * @param array $sections
     * @return void
     */
    public function __construct( array $sections )
    {
        $this->sections = $sections;
    }
}

