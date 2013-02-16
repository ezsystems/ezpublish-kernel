<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlAlias\SlugConverterTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlAlias;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter;

/**
 * Test case for URL slug converter.
 */
class SlugConverterTest extends TestCase
{
    /**
     * Test for the __construct() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter::__construct
     */
    public function testConstructor()
    {
        $slugConverter = $this->getMockedSlugConverter();

        $this->assertAttributeSame(
            $this->getTransformationProcessorMock(),
            'transformationProcessor',
            $slugConverter
        );

        $this->assertAttributeInternalType(
            "array",
            'configuration',
            $slugConverter
        );
    }

    /**
     * Test for the convert() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter::convert
     */
    public function testConvert()
    {
        $slugConverter = $this->getSlugConverterMock( array( "cleanupText" ) );
        $transformationProcessor = $this->getTransformationProcessorMock();

        $text = "test text  č ";
        $transformedText = "test text  c ";
        $slug = "test_text_c";

        $transformationProcessor->expects( $this->atLeastOnce() )
            ->method( "transform" )
            ->with( $text, array( "test_command1" ) )
            ->will( $this->returnValue( $transformedText ) );

        $slugConverter->expects( $this->once() )
            ->method( "cleanupText" )
            ->with( $this->equalTo( $transformedText ), $this->equalTo( "test_cleanup1" ) )
            ->will( $this->returnValue( $slug ) );

        $this->assertEquals(
            $slug,
            $slugConverter->convert( $text )
        );
    }

    /**
     * Test for the convert() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter::convert
     */
    public function testConvertWithDefaultTextFallback()
    {
        $slugConverter = $this->getSlugConverterMock( array( "cleanupText" ) );
        $transformationProcessor = $this->getTransformationProcessorMock();

        $defaultText = "test text  č ";
        $transformedText = "test text  c ";
        $slug = "test_text_c";

        $transformationProcessor->expects( $this->atLeastOnce() )
            ->method( "transform" )
            ->with( $defaultText, array( "test_command1" ) )
            ->will( $this->returnValue( $transformedText ) );

        $slugConverter->expects( $this->once() )
            ->method( "cleanupText" )
            ->with( $this->equalTo( $transformedText ), $this->equalTo( "test_cleanup1" ) )
            ->will( $this->returnValue( $slug ) );

        $this->assertEquals(
            $slug,
            $slugConverter->convert( "", $defaultText )
        );
    }

    /**
     * Test for the convert() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter::convert
     */
    public function testConvertWithGivenTransformation()
    {
        $slugConverter = $this->getSlugConverterMock( array( "cleanupText" ) );
        $transformationProcessor = $this->getTransformationProcessorMock();

        $text = "test text  č ";
        $transformedText = "test text  c ";
        $slug = "test_text_c";

        $transformationProcessor->expects( $this->atLeastOnce() )
            ->method( "transform" )
            ->with( $text, array( "test_command2" ) )
            ->will( $this->returnValue( $transformedText ) );

        $slugConverter->expects( $this->once() )
            ->method( "cleanupText" )
            ->with( $this->equalTo( $transformedText ), $this->equalTo( "test_cleanup2" ) )
            ->will( $this->returnValue( $slug ) );

        $this->assertEquals(
            $slug,
            $slugConverter->convert( $text, "_1", "testTransformation2" )
        );
    }

    public function providerForTestGetUniqueCounterValue()
    {
        return array(
            array( "reserved", true, 2 ),
            array( "reserved", false, 1 ),
            array( "not-reserved", true, 1 ),
            array( "not-reserved", false, 1 ),
        );
    }

    /**
     * Test for the getUniqueCounterValue() method.
     *
     * @dataProvider providerForTestGetUniqueCounterValue
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter::getUniqueCounterValue
     */
    public function testGetUniqueCounterValue( $text, $isRootLevel, $returnValue )
    {
        $slugConverter = $this->getMockedSlugConverter();

        $this->assertEquals(
            $returnValue,
            $slugConverter->getUniqueCounterValue( $text, $isRootLevel )
        );
    }

    /**
     * @var array
     */
    protected $configuration = array(
        "transformation" => "testTransformation1",
        "transformationGroups" => array(
            "testTransformation1" => array(
                "commands" => array(
                    "test_command1",
                ),
                "cleanupMethod" => "test_cleanup1",
            ),
            "testTransformation2" => array(
                "commands" => array(
                    "test_command2",
                ),
                "cleanupMethod" => "test_cleanup2",
            ),
        ),
        "reservedNames" => array(
            "reserved",
        ),
    );

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter
     */
    protected $slugConverter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $slugConverterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transformationProcessorMock;

    /**
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter
     */
    protected function getMockedSlugConverter()
    {
        if ( !isset( $this->slugConverter ) )
        {
            $this->slugConverter = new SlugConverter(
                $this->getTransformationProcessorMock(),
                $this->configuration
            );
        }

        return $this->slugConverter;
    }

    /**
     * @param array $methods
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSlugConverterMock( array $methods = array() )
    {
        if ( !isset( $this->slugConverterMock ) )
        {
            $this->slugConverterMock = $this->getMock(
                "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\UrlAlias\\SlugConverter",
                $methods,
                array(
                    $this->getTransformationProcessorMock(),
                    $this->configuration
                )
            );
        }

        return $this->slugConverterMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTransformationProcessorMock()
    {
        if ( !isset( $this->transformationProcessorMock ) )
        {
            $this->transformationProcessorMock = $this->getMockForAbstractClass(
                "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Search\\TransformationProcessor",
                array(),
                '',
                false,
                true,
                true,
                array( "transform" )
            );
        }

        return $this->transformationProcessorMock;
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
