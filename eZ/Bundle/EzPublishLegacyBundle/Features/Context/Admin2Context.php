<?php
/**
 * File containing the Admin2Context class for the LegacyBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Features\Context;

class Admin2Context extends LegacyContext
{
    public function __construct( array $parameters )
    {
        parent::__construct( $parameters );

        $this->pageIdentifierMap += array(
            'content structure' => '/'
        );

        $this->pageIdentifierMap['login'] = '/user/login';
        $this->pageIdentifierMap['logout'] = '/user/logout';
    }
}
