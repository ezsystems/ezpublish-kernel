<?php
/**
 * File containing the Services controller class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\Core\REST\Server\Controller as RestController;
use eZ\Publish\Core\REST\Server\Values;

/**
 * Services controller
 */
class Services extends RestController
{
    /**
     * @var array
     */
    protected $countriesInfo;

    public function __construct( array $countriesInfo )
    {
        $this->countriesInfo = $countriesInfo;
    }
    /**
     * Loads Country List
     *
     */
    public function loadCountryList()
    {
        return new Values\CountryList( $this->countriesInfo );
    }

}
