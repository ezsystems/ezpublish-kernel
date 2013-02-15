<?php
/**
 * File containing the GlobalHelper class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating;

use eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper as BaseGlobalHelper;

class GlobalHelper extends BaseGlobalHelper
{
    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Templating\LegacyHelper
     */
    public function getLegacy()
    {
        return $this->container->get( 'ezpublish_legacy.templating.legacy_helper' );
    }
}
