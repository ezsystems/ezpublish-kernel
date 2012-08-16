<?php
/**
 * File containing the BinaryFileTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\BinaryFile\Type as BinaryFileType,
    eZ\Publish\Core\FieldType\BinaryFile\Value as BinaryFileValue;

/**
 * @group fieldType
 * @group ezbinaryfile
 */
class BinaryFileTest extends FieldTypeTest
{
    protected $validatorServiceMock;

    protected $fieldTypeToolsMock;

    protected $fileServiceMock;

    protected $mimeTypeDetectorMock;

    protected $binaryFileType;

    protected function getBinaryFileType()
    {
        if ( !isset( $this->binaryFileType ) )
        {
            $this->binaryFileType = new BinaryFileType(
                $this->getValidatorServiceMock(),
                $this->getFieldTypeToolsMock(),
                $this->getFileServiceMock(),
                $this->getMimeTypeDetectorMock()
            );
        }
        return $this->binaryFileType;
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testValidatorConfigurationSchema()
    {
        $fieldType = $this->getBinaryFileType();

        self::assertSame(
            array(
                "FileSizeValidator" => array(
                    "maxFileSize" => array(
                        "type" => "int",
                        "default" => false
                    )
                )
            ),
            $fieldType->getValidatorConfigurationSchema(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getSettingsSchema
     */
    public function testSettingsSchema()
    {
        $fieldType = $this->getBinaryFileType();
        self::assertSame(
            array(),
            $fieldType->getSettingsSchema(),
            "The settings schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\BinaryFile\Type::getEmptyValue
     */
    public function testEmptyValue()
    {
        $fieldType = $this->getBinaryFileType();

        $this->assertNull( $fieldType->getEmptyValue() );
    }

    /**
     * @param mixed $inputValue
     * @param mixed $expectedException
     * @covers \eZ\Publish\Core\FieldType\BinaryFile\Type::acceptValue
     * @dataProvider provideInvalidInputValues
     */
    public function testAcceptValueFailsOnInvalidValues( $inputValue, $expectedException )
    {
        $fieldType = $this->getBinaryFileType();

        try
        {
            $fieldType->acceptValue( $inputValue );
            $this->fail(
                sprintf(
                    'Expected exception of type "%s" not thrown.',
                    $expectedException
                )
            );
        }
        catch ( \Exception $e )
        {
            $this->assertInstanceOf(
                $expectedException,
                $e
            );
        }
    }

    public function provideInvalidInputValues()
    {
        return array(
            array(
                new \stdClass(),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                array(),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                new BinaryFileValue(),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                array( 'path' => '/foo/bar' ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                new BinaryFileValue( array( 'path' => '/foo/bar' ) ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
        );
    }

    /**
     * @param mixed $inputValue
     * @param mixed $expectedOutputValue
     * @dataProvider provideValidInputValues
     * @covers \eZ\Publish\Core\FieldType\BinaryFile\Type::acceptValue
     */
    public function testAcceptValue( $inputValue, $expectedOutputValue )
    {
        $fieldType = $this->getBinaryFileType();

        $this->getMimeTypeDetectorMock()->expects( $this->any() )
            ->method( 'getMimeType' )
            ->will( $this->returnValue( 'text/plain' ) );

        $outputValue = $fieldType->acceptValue( $inputValue );

        $this->assertEquals(
            $expectedOutputValue,
            $outputValue,
            'acceptValue() did not convert properly.'
        );
    }

    public function provideValidInputValues()
    {
        return array(
            array(
                null,
                null
            ),
            array(
                __FILE__,
                new BinaryFileValue( array(
                    'path' => __FILE__,
                    'fileName' => basename( __FILE__ ),
                    'fileSize' => filesize( __FILE__ ),
                    'downloadCount' => 0,
                    'mimeType' => 'text/plain',
                ) )
            ),
            array(
                array( 'path' => __FILE__ ),
                new BinaryFileValue( array(
                    'path' => __FILE__,
                    'fileName' => basename( __FILE__ ),
                    'fileSize' => filesize( __FILE__ ),
                    'downloadCount' => 0,
                    'mimeType' => 'text/plain',
                ) )
            ),
            array(
                array(
                    'path' => __FILE__,
                    'fileSize' => 23,
                ),
                new BinaryFileValue( array(
                    'path' => __FILE__,
                    'fileName' => basename( __FILE__ ),
                    'fileSize' => 23,
                    'downloadCount' => 0,
                    'mimeType' => 'text/plain',
                ) )
            ),
            array(
                array(
                    'path' => __FILE__,
                    'downloadCount' => 42,
                ),
                new BinaryFileValue( array(
                    'path' => __FILE__,
                    'fileName' => basename( __FILE__ ),
                    'fileSize' => filesize( __FILE__ ),
                    'downloadCount' => 42,
                    'mimeType' => 'text/plain',
                ) )
            ),
            array(
                array(
                    'path' => __FILE__,
                    'mimeType' => 'application/text+php',
                ),
                new BinaryFileValue( array(
                    'path' => __FILE__,
                    'fileName' => basename( __FILE__ ),
                    'fileSize' => filesize( __FILE__ ),
                    'downloadCount' => 0,
                    'mimeType' => 'application/text+php',
                ) )
            ),
        );
    }

    protected function getValidatorServiceMock()
    {
        if ( !isset( $this->validatorServiceMock ) )
        {
            $this->validatorServiceMock = $this->getMock(
                'eZ\\Publish\\Core\\Repository\\ValidatorService',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->validatorServiceMock;
    }

    protected function getFieldTypeToolsMock()
    {
        if ( !isset( $this->fieldTypeToolsMock ) )
        {
            $this->fieldTypeToolsMock = $this->getMock(
                'eZ\\Publish\\Core\\Repository\\FieldTypeTools',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->fieldTypeToolsMock;
    }

    protected function getFileServiceMock()
    {
        if ( !isset( $this->fileServiceMock ) )
        {
            $this->fileServiceMock = $this->getMock(
                'eZ\\Publish\\Core\\FieldType\\FileService',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->fileServiceMock;
    }

    protected function getMimeTypeDetectorMock()
    {
        if ( !isset( $this->mimeTypeDetectorMock ) )
        {
            $this->mimeTypeDetectorMock = $this->getMock(
                'eZ\\Publish\\Core\\FieldType\\BinaryFile\\MimeTypeDetector',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->mimeTypeDetectorMock;
    }
}
