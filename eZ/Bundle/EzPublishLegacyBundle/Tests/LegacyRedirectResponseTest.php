<?php
/**
 * File containing the LegacyRedirectResponseTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Tests;

use eZ\Bundle\EzPublishLegacyBundle\LegacyResponse;

class LegacyRedirectResponseTest extends LegacyResponseTest
{
    public function generateMockResponse()
    {
        return $this->getMockBuilder( 'eZ\Bundle\EzPublishLegacyBundle\LegacyRedirectResponse' )
                ->setMethods( array( 'removeHeader' ) )
                ->setConstructorArgs( array( 'http://om.net', 302 ) )
                ->getMock();
    }

    // Test method is in LegacyResponseTest
}
