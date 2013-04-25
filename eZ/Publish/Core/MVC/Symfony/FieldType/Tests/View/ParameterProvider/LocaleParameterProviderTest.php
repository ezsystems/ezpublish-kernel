<?php
/**
 * File containing the ParameterProviderTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\FieldType\Tests\View\ParameterProvider;

use eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProvider\LocaleParameterProvider;
use eZ\Publish\API\Repository\Values\Content\Field;

class LocaleParameterProviderTest extends \PHPUnit_Framework_TestCase
{
    public function providerForTestGetViewParameters()
    {
        return array(
            array( true, "fr_FR" ),
            array( false, "hr_HR" ),
        );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProvider\LocaleParameterProvider::getViewParameters
     * @dataProvider providerForTestGetViewParameters
     */
    public function testGetViewParameters( $hasRequestLocale, $expectedLocale )
    {
        $field = new Field( array( "languageCode" => "cro-HR" ) );
        $parameterProvider = new LocaleParameterProvider(
            $this->getContainerMock( $hasRequestLocale ),
            $this->getLocaleConverterMock()
        );
        $this->assertSame(
            array( 'locale' => $expectedLocale ),
            $parameterProvider->getViewParameters( $field )
        );
    }

    protected function getContainerMock( $hasRequestLocale )
    {
        $mock = $this->getMock(
            'Symfony\\Component\\DependencyInjection\\ContainerInterface'
        );

        $mock->expects( $this->any() )
            ->method( "get" )
            ->with( $this->equalTo( "request" ) )
            ->will( $this->returnValue( $this->getRequestMock( $hasRequestLocale ) ) );

        return $mock;
    }

    protected function getRequestMock( $hasLocale )
    {
        $parameterBagMock = $this->getMock( "Symfony\\Component\\HttpFoundation\\ParameterBag" );

        $parameterBagMock->expects( $this->any() )
            ->method( "has" )
            ->with( $this->equalTo( "_locale" ) )
            ->will( $this->returnValue( $hasLocale ) );

        $parameterBagMock->expects( $this->any() )
            ->method( "get" )
            ->with( $this->equalTo( "_locale" ) )
            ->will( $this->returnValue( "fr_FR" ) );

        $mock = $this->getMock( "Symfony\\Component\\HttpFoundation\\Request" );
        $mock->attributes = $parameterBagMock;

        $mock->expects( $this->any() )
            ->method( "__get" )
            ->with( $this->equalTo( "attributes" ) )
            ->will( $this->returnValue( $parameterBagMock ) );

        return $mock;
    }

    protected function getLocaleConverterMock()
    {
        $mock = $this->getMock( "eZ\\Publish\\Core\\MVC\\Symfony\\Locale\\LocaleConverterInterface" );

        $mock->expects( $this->any() )
            ->method( "convertToPOSIX" )
            ->with( $this->equalTo( "cro-HR" ) )
            ->will( $this->returnValue( "hr_HR" ) );

        return $mock;
    }
}
