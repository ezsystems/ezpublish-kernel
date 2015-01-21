<?php
/**
 * File containing the GlobalHelper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating;

use eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper as BaseGlobalHelper;

class GlobalHelper extends BaseGlobalHelper
{
    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Templating\LegacyHelper
     */
    protected $legacyHelper;

    /**
     * @param \eZ\Publish\Core\MVC\Legacy\Templating\LegacyHelper $legacyHelper
     */
    public function setLegacyHelper( LegacyHelper $legacyHelper )
    {
        $this->legacyHelper = $legacyHelper;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Templating\LegacyHelper
     */
    public function getLegacy()
    {
        return $this->legacyHelper;
    }
}
