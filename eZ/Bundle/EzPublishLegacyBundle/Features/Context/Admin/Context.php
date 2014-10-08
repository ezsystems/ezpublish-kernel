<?php
/**
 * File containing the Context class for the Legacy Admin interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Features\Context\Admin;

use eZ\Bundle\EzPublishLegacyBundle\Features\Context\Legacy;

class Context extends Legacy
{
    public function __construct()
    {
        parent::__construct();

        $this->pageIdentifierMap['login'] = '/user/login';
        $this->pageIdentifierMap['logout'] = '/user/logout';
    }
}
