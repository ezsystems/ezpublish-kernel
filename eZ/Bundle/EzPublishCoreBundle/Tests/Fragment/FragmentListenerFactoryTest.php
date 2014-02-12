<?php
/**
 * File containing the FragmentListenerFactoryTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Fragment;

use eZ\Bundle\EzPublishCoreBundle\Fragment\FragmentListenerFactory;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\UriSigner;
use ReflectionObject;

class FragmentListenerFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider buildFragmentListenerProvider
     */
    public function testBuildFragmentListener( $requestUri, $isFragmentCandidate )
    {
        $listenerClass = 'Symfony\Component\HttpKernel\EventListener\FragmentListener';
        $uriSigner = new UriSigner( 'my_precious_secret' );
        $baseFragmentPath = '/_fragment';
        $request = Request::create( $requestUri );

        $factory = new FragmentListenerFactory();
        $factory->setRequest( $request );
        $listener = $factory->buildFragmentListener( $uriSigner, $baseFragmentPath, $listenerClass );
        $this->assertInstanceOf( $listenerClass, $listener );

        $refListener = new ReflectionObject( $listener );
        $refFragmentPath = $refListener->getProperty( 'fragmentPath' );
        $refFragmentPath->setAccessible( true );
        if ( $isFragmentCandidate )
        {
            $this->assertSame( $requestUri, $refFragmentPath->getValue( $listener ) );
        }
        else
        {
            $this->assertSame( $baseFragmentPath, $refFragmentPath->getValue( $listener ) );
        }
    }

    public function buildFragmentListenerProvider()
    {
        return array(
            array( '/foo/bar', false ),
            array( '/foo', false ),
            array( '/_fragment', true ),
            array( '/my_siteaccess/_fragment', true ),
            array( '/foo/_fragment/something', false ),
            array( '/_fragment/something', false ),
        );
    }
}
