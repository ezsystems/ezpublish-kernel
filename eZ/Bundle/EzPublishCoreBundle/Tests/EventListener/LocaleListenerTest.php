<?php
/**
 * File containing the LocaleListenerTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\LocaleListener;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class LocaleListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var LocaleConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeConverter;

    /**
     * @var ConfigResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' );
        $this->localeConverter = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface' );
        $this->configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
    }

    /**
     * @dataProvider onKernelRequestProvider
     */
    public function testOnKernelRequest( array $configuredLanguages, array $convertedLocalesValueMap, $expectedLocale )
    {
        $this->container
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'ezpublish.config.resolver' )
            ->will( $this->returnValue( $this->configResolver ) );

        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'languages' )
            ->will( $this->returnValue( $configuredLanguages ) );
        $this->localeConverter
            ->expects( $this->atLeastOnce() )
            ->method( 'convertToPOSIX' )
            ->will(
                $this->returnValueMap( $convertedLocalesValueMap )
            );

        $defaultLocale = 'en';
        $localeListener = new LocaleListener( $defaultLocale );
        $localeListener->setServiceContainer( $this->container );
        $localeListener->setLocaleConverter( $this->localeConverter );

        $request = new Request();
        $localeListener->onKernelRequest(
            new GetResponseEvent(
                $this->getMock( 'Symfony\Component\HttpKernel\HttpKernelInterface' ),
                $request,
                HttpKernelInterface::MASTER_REQUEST
            )
        );
        $this->assertSame( $expectedLocale, $request->attributes->get( '_locale' ) );
    }

    public function onKernelRequestProvider()
    {
        return array(
            array(
                array( 'eng-GB' ),
                array(
                    array( 'eng-GB', 'en_GB' ),
                ),
                'en_GB'
            ),
            array(
                array( 'eng-DE' ),
                array(
                    array( 'eng-DE', null ),
                ),
                // Default locale
                null
            ),
            array(
                array( 'fre-CA', 'fre-FR', 'eng-US' ),
                array(
                    array( 'fre-CA', null ),
                    array( 'fre-FR', 'fr_FR' ),
                ),
                'fr_FR'
            ),
            array(
                array( 'fre-CA', 'fre-FR', 'eng-US' ),
                array(
                    array( 'fre-CA', null ),
                    array( 'fre-FR', null ),
                    array( 'eng-US', null ),
                ),
                null
            ),
            array(
                array( 'esl-ES', 'eng-GB' ),
                array(
                    array( 'esl-ES', 'es_ES' ),
                    array( 'eng-GB', 'en_GB' ),
                ),
                'es_ES'
            ),
        );
    }
}
