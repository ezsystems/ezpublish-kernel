<?php
/**
 * File containing the BinaryBaseTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\BinaryBase\Type as BinaryBaseType;
use eZ\Publish\Core\FieldType\BinaryBase\Value as BinaryBaseValue;
use eZ\Publish\SPI\FieldType\BinaryBase\MimeTypeDetector;
use \eZ\Publish\SPI\FieldType\FileService;

/**
 * Base class for binary field types
 *
 * @group fieldType
 */
abstract class BinaryBaseTest extends FieldTypeTest
{
    /** @var FileService */
    private $IOServiceMock;

    /** @var MimeTypeDetector */
    private $mimeTypeDetectorMock;

    protected function getValidatorConfigurationSchemaExpectation()
    {
        return array(
            "FileSizeValidator" => array(
                "maxFileSize" => array(
                    "type" => "int",
                    "default" => false
                )
            )
        );
    }

    protected function getSettingsSchemaExpectation()
    {
        return array();
    }

    /**
     * @param mixed  $inputValue
     * @param mixed  $expectedOutputValue
     * @param array  $IOServiceExpectations
     *        An array indexed by {@see FileService} method name, with for each method one value that will be returned
     * @param null   $mimeTypeDetectorExpectations
     *        An array indexed by {@see MimeTypeDetectorr} method name, with for each method one value that will be returned
     *
     * @return void
     *
     * @dataProvider provideValidInputForAcceptValue
     */
    public function testAcceptValue( $inputValue, $expectedOutputValue, $IOServiceExpectations = null, $mimeTypeDetectorExpectations = null )
    {
        /** @var $fieldType BinaryBaseType */
        $fieldType = $this->createFieldTypeUnderTest();

        // add custom expectations to the FileService mock
        if ( count( $IOServiceExpectations ) )
        {
            /** @var $fieldType BinaryBaseType */
            $fieldType = $this->createFieldTypeUnderTest();

            /** @var $fileServiceMock \PHPUnit_Framework_MockObject_MockObject */
            $IOServiceMock = $this->getIOServiceMock();

            foreach ( $IOServiceExpectations as $method => $value )
            {
                $IOServiceMock->expects( $this->once() )
                    ->method( $method )
                    ->will( $this->returnValue( $value ) );
            }
        }

        // add custom expectations to the MimeTypeDetector mock
        if ( count( $mimeTypeDetectorExpectations ) )
        {
            /** @var $mimeTypeDetectorMock \PHPUnit_Framework_MockObject_MockObject */
            $mimeTypeDetectorMock = $this->getMimeTypeDetectorMock();

            foreach ( $mimeTypeDetectorExpectations as $method => $value )
            {
                $mimeTypeDetectorMock->expects( $this->once() )
                    ->method( $method )
                    ->will( $this->returnValue( $value ) );
            }
        }

        parent::testAcceptValue( $inputValue, $expectedOutputValue );
    }

    public function provideInvalidInputForAcceptValue()
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
                array( 'path' => '/foo/bar' ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
        );
    }

    /**
     * Provide data sets with validator configurations which are considered
     * valid by the {@link validateValidatorConfiguration()} method.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of validator configurations.
     *
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(),
     *      ),
     *      array(
     *          array(
     *              'IntegerValueValidator' => array(
     *                  'minIntegerValue' => 0,
     *                  'maxIntegerValue' => 23,
     *              )
     *          )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideValidValidatorConfiguration()
    {
        return array(
            array(
                array()
            ),
            array(
                array(
                    'FileSizeValidator' => array(
                        'maxFileSize' => 2342,
                    )
                )
            ),
            array(
                array(
                    'FileSizeValidator' => array(
                        'maxFileSize' => false,
                    )
                )
            ),
        );
    }

    /**
     * Provide data sets with validator configurations which are considered
     * invalid by the {@link validateValidatorConfiguration()} method. The
     * method must return a non-empty array of validation errors when receiving
     * one of the provided values.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of validator configurations.
     *
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(
     *              'NonExistentValidator' => array(),
     *          ),
     *      ),
     *      array(
     *          array(
     *              // Typos
     *              'InTEgervALUeVALIdator' => array(
     *                  'minIntegerValue' => 0,
     *                  'maxIntegerValue' => 23,
     *              )
     *          )
     *      ),
     *      array(
     *          array(
     *              'IntegerValueValidator' => array(
     *                  // Incorrect value types
     *                  'minIntegerValue' => true,
     *                  'maxIntegerValue' => false,
     *              )
     *          )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInvalidValidatorConfiguration()
    {
        return array(
            array(
                array(
                    'NonExistingValidator' => array()
                )
            ),
            array(
                // maxFileSize must be int or bool
                array(
                    'FileSizeValidator' => array(
                        'maxFileSize' => 'foo',
                    )
                )
            ),
            array(
                // maxFileSize is required for this validator
                array(
                    'FileSizeValidator' => array()
                )
            ),
        );
    }

    /**
     * @return MimeTypeDetector
     */
    protected function getMimeTypeDetectorMock()
    {
        if ( !isset( $this->mimeTypeDetectorMock ) )
        {
            $this->mimeTypeDetectorMock = $this->getMock(
                'eZ\\Publish\\SPI\\FieldType\\BinaryBase\\MimeTypeDetector',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->mimeTypeDetectorMock;
    }

    /**
     * Returns a mock for the FileService
     *
     * @return \eZ\Publish\Core\FieldType\FileService
     */
    protected function getIOServiceMock()
    {
        if ( !isset( $this->IOServiceMock ) )
        {
            $this->IOServiceMock = $this->getMock(
                'eZ\\Publish\\Core\\IO\\IOService',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->IOServiceMock;
    }
}
