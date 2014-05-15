<?php
/**
 * File containing the HttpCacheTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests;

use eZ\Bundle\EzPublishCoreBundle\HttpCache;
use eZ\Bundle\EzPublishCoreBundle\Kernel;
use Symfony\Component\HttpFoundation\Request;

class HttpCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateUserHashNotAllowed()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|HttpCache $kernelCache */
        $kernelCache = $this
            ->getMockBuilder( 'eZ\\Bundle\\EzPublishCoreBundle\\HttpCache' )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $request = new Request();
        $request->headers->add(
            array(
                'X-HTTP-Override' => 'AUTHENTICATE',
                'Accept' => Kernel::USER_HASH_ACCEPT_HEADER
            )
        );
        $response = $kernelCache->handle( $request );
        $this->assertInstanceOf( 'Symfony\\Component\\HttpFoundation\\Response', $response );
        $this->assertSame( 405, $response->getStatusCode() );
    }
}
