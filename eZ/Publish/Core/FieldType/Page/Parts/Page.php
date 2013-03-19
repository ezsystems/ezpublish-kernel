<?php
/**
 * File containing the Page class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page\Parts;

/**
 * @property-read string $layout The layout identifier (e.g. "2ZonesLayout1").
 * @property-read \eZ\Publish\Core\FieldType\Page\Parts\Zone[] $zones Zone objects for current page, numerically indexed.
 * @property-read \eZ\Publish\Core\FieldType\Page\Parts\Zone[] $zonesById Zone objects for current page, indexed by their Id.
 */
class Page extends Base
{
    /**
     * @var \eZ\Publish\Core\FieldType\Page\Parts\Zone[]
     */
    protected $zones = array();

    /**
     * @var array
     */
    protected $zonesById = array();

    /**
     * @var string
     */
    protected $layout;

    public function __construct( array $properties = array() )
    {
        parent::__construct( $properties );

        foreach ( $this->zones as $zone )
        {
            $this->zonesById[$zone->id] = $zone;
        }
    }
}
