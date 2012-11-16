<?php
/**
 * File containing the BinaryBaseTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\BinaryBase\Type as BinaryBaseType,
    eZ\Publish\Core\FieldType\BinaryBase\Value as BinaryBaseValue;

/**
 * Base class for binary field types
 *
 * @group fieldType
 */
abstract class BinaryBaseTest extends StandardizedFieldTypeTest
{
    private $mimeTypeDetectorMock;

    public function setUp()
    {
        parent::setUp();

        $this->getMimeTypeDetectorMock()->expects( $this->any() )
            ->method( 'getMimeType' )
            ->will( $this->returnValue( 'text/plain' ) );
    }

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
     * method must return a non-empty array of valiation errors when receiving
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

    protected function getMimeTypeDetectorMock()
    {
        if ( !isset( $this->mimeTypeDetectorMock ) )
        {
            $this->mimeTypeDetectorMock = $this->getMock(
                'eZ\\Publish\\Core\\FieldType\\BinaryBase\\MimeTypeDetector',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->mimeTypeDetectorMock;
    }
}
