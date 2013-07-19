<?php
/**
 * File containing the Html5 converter test
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter\Html5;
use PHPUnit_Framework_TestCase;
use DomDocument;

/**
 * Tests the Html5 converter
 */
class Html5Test extends PHPUnit_Framework_TestCase
{

    protected function getDefaultStylesheet()
    {
        return __DIR__ . '../../../../XmlText/Input/Resources/stylesheets/eZXml2Html5_core.xsl';
    }

    protected function getPreConvertMock() 
    {
        return $this->getMockBuilder( 'eZ\Publish\Core\FieldType\XmlText\Converter' )
            ->getMock();
    }

    public function dataProviderConstructorException()
    {
        return array(
            array(
                array( 1, 2 ),
                array( 1, $this->getPreConvertMock() ),
                array( $this->getPreConvertMock(), 1 )
            )
        );
    }

    /**
     * @dataProvider dataProviderConstructorException
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function testConstructorException( array $preConverters )
    {
        new Html5( '', $preConverters );
    }


    public function testPreConverterCalled()
    {
        $dom = new DomDocument();
        $preConverterMock1 = $this->getPreConvertMock();
        $preConverterMock2 = $this->getPreConvertMock();

        $preConverterMock1->expects( $this->once() )
            ->method( 'convert' )
            ->with( $this->equalTo( $dom ) );

        $preConverterMock2->expects( $this->once() )
            ->method( 'convert' )
            ->with( $this->equalTo( $dom ) );

        $html5 = new Html5(
            $this->getDefaultStylesheet(),
            array(
                $preConverterMock1,
                $preConverterMock2
            )
        );
        $html5->convert( $dom );
    }
}
