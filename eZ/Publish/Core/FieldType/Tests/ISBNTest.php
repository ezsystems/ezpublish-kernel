<?php
/**
 * File containing the ISBNTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU General Public License v2.0
 * @version 
 */

namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\ISBN\Type as ISBN;
use eZ\Publish\Core\FieldType\ISBN\Value as ISBNValue;
use eZ\Publish\Core\FieldType\ValidationError;

/**
 * @group fieldType
 * @group ezisbn
 */
class ISBNTest extends FieldTypeTest
{
    /**
     * Returns the field type under test.
     *
     * This method is used by all test cases to retrieve the field type under
     * test. Just create the FieldType instance using mocks from the provided
     * get*Mock() methods and/or custom get*Mock() implementations. You MUST
     * NOT take care for test case wide caching of the field type, just return
     * a new instance from this method!
     *
     * @return FieldType
     */
    protected function createFieldTypeUnderTest()
    {
        $fieldType = new ISBN( "9789722514095" );
        $fieldType->setTransformationProcessor( $this->getTransformationProcessorMock() );

        return $fieldType;
    }

    /**
     * Returns the validator configuration schema expected from the field type.
     *
     * @return array
     */
    protected function getValidatorConfigurationSchemaExpectation()
    {
        return array();
    }

    /**
     * Returns the settings schema expected from the field type.
     *
     * @return array
     */
    protected function getSettingsSchemaExpectation()
    {
        return array(
            "isISBN13" => array(
                "type" => "boolean",
                "default" => true
            )
        );
    }

    /**
     * Returns the empty value expected from the field type.
     *
     * @return \eZ\Publish\Core\FieldType\Checkbox\Value
     */
    protected function getEmptyValueExpectation()
    {
        return new ISBNValue;
    }

    /**
     * Data provider for invalid input to acceptValue().
     *
     * Returns an array of data provider sets with 2 arguments: 1. The invalid
     * input to acceptValue(), 2. The expected exception type as a string. For
     * example:
     *
     * <code>
     *  return array(
     *      array(
     *          new \stdClass(),
     *          'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
     *      ),
     *      array(
     *          array(),
     *          'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInvalidInputForAcceptValue()
    {
        return array(
            array(
                1234567890,
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                "1234567890",
                'eZ\\Publish\\Core\\FieldType\\ISBN\\Exception\\InvalidValue',
            ),
            array(
                "here is an invalid isbn",
                'eZ\\Publish\\Core\\FieldType\\ISBN\\Exception\\InvalidValue',
            ),
            array(
                "9789722514093",
                'eZ\\Publish\\Core\\FieldType\\ISBN\\Exception\\InvalidValue',
            ),
            array(
                "3789722514095",
                'eZ\\Publish\\Core\\FieldType\\ISBN\\Exception\\InvalidValue',
            ),
        );
    }

    /**
     * Data provider for valid input to acceptValue().
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to acceptValue(), 2. The expected return value from acceptValue().
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          null,
     *          null
     *      ),
     *      array(
     *          __FILE__,
     *          new BinaryFileValue( array(
     *              'path' => __FILE__,
     *              'fileName' => basename( __FILE__ ),
     *              'fileSize' => filesize( __FILE__ ),
     *              'downloadCount' => 0,
     *              'mimeType' => 'text/plain',
     *          ) )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideValidInputForAcceptValue()
    {
        return array(
            array(
                "9789722514095",
                new ISBNValue( "9789722514095" )
            ),
            array(
                "978-972-25-1409-5",
                new ISBNValue( "978-972-25-1409-5" )
            ),
            array(
                "0-9752298-0-X",
                new ISBNValue( "0-9752298-0-X" )
            ),
        );
    }

    /**
     * Provide input for the toHash() method
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to toHash(), 2. The expected return value from toHash().
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          null,
     *          null
     *      ),
     *      array(
     *          new BinaryFileValue( array(
     *              'path' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ) ),
     *          array(
     *              'path' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInputForToHash()
    {
        return array(
            array(
                new ISBNValue( "9789722514095" ),
                "9789722514095"
            ),
        );
    }

    /**
     * Provide input to fromHash() method
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to fromHash(), 2. The expected return value from fromHash().
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          null,
     *          null
     *      ),
     *      array(
     *          array(
     *              'path' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ),
     *          new BinaryFileValue( array(
     *              'path' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ) )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInputForFromHash()
    {
        return array(
            array(
                "9789722514095",
                new ISBNValue( "9789722514095" )
            ),
        );
    }

    protected function provideFieldTypeIdentifier()
    {
        return 'ezisbn';
    }

    public function provideDataForGetName()
    {
        return array(
            array( $this->getEmptyValueExpectation(), "" ),
            array( new ISBNValue( "9789722514095" ), "9789722514095" )
        );
    }

    /**
     * Provides data sets with validator configuration and/or field settings and
     * field value which are considered valid by the {@link validate()} method.
     *
     * @return array
     */
    public function provideValidDataForValidate()
    {
        return array(
            array(
                array(
                    "fieldSettings" => array(
                        'isISBN13' => true
                    ),
                ),
                new ISBNValue(),
            ),
            array(
                array(
                    "fieldSettings" => array(
                        'isISBN13' => false
                    ),
                ),
                new ISBNValue(),
            ),
            array(
                array(
                    "fieldSettings" => array(
                        'isISBN13' => true
                    ),
                ),
                new ISBNValue( "9789722514095" ),
            ),
            array(
                array(
                    "fieldSettings" => array(
                        'isISBN13' => false
                    ),
                ),
                new ISBNValue( "0-9752298-0-X" ),
            ),
        );
    }

    /**
     * Provides data sets with validator configuration and/or field settings,
     * field value and corresponding validation errors returned by
     * the {@link validate()} method.
     *
     * @return array
     */
    public function provideInvalidDataForValidate()
    {
        return array(
            array(
                array(
                    "fieldSettings" => array(
                        'isISBN13' => false
                    ),
                ),
                new ISBNValue( "9789722514095" ),
                array(
                    new ValidationError(
                        "Field definition limits ISBN to ISBN10."
                    ),
                ),
            ),
        );
    }
}
