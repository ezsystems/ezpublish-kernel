<?php
/**
 * File containing the RelationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\RelationList\Type as RelationList;
use eZ\Publish\Core\FieldType\RelationList\Value;
use eZ\Publish\Core\FieldType\Tests\FieldTypeTest;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\API\Repository\Values\Content\Relation;
use PHPUnit_Framework_TestCase;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class RelationListTest extends StandardizedFieldTypeTest
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
     * @return \eZ\Publish\Core\FieldType\Relation\Type
     */
    protected function createFieldTypeUnderTest()
    {
        return new RelationList();
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
            'selectionMethod' => array(
                'type' => 'int',
                'default' => RelationList::SELECTION_BROWSE,
            ),
            'selectionDefaultLocation' => array(
                'type' => 'string',
                'default' => null,
            ),
            'selectionContentTypes' => array(
                'type' => 'array',
                'default' => array(),
            ),
        );
    }

    /**
     * Returns the empty value expected from the field type.
     *
     * @return void
     */
    protected function getEmptyValueExpectation()
    {
        // @todo FIXME: Is this correct?
        return new Value();
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
                true,
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            )
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
                new Value(),
                new Value(),
            ),
            array(
                23,
                new Value( array( 23 ) ),
            ),
            array(
                new ContentInfo( array( 'id' => 23 ) ),
                new Value( array( 23 ) ),
            ),
            array(
                array( 23, 42 ),
                new Value( array( 23, 42 ) ),
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
                new Value( array( 23, 42 ) ),
                array( 'destinationContentIds' => array( 23, 42 ) ),
            ),
            array(
                new Value(),
                array( 'destinationContentIds' => array() ),
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
                array( 'destinationContentIds' => array( 23, 42 ) ),
                new Value( array( 23, 42 ) ),
            ),
            array(
                array( 'destinationContentIds' => array() ),
                new Value(),
            ),
        );
    }

    /**
     * Provide data sets with field settings which are considered valid by the
     * {@link validateFieldSettings()} method.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of field settings.
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(),
     *      ),
     *      array(
     *          array( 'rows' => 2 )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideValidFieldSettings()
    {
        return array(
            array(
                array(
                    'selectionMethod' => RelationList::SELECTION_BROWSE,
                    'selectionDefaultLocation' => 23,
                )
            ),
            array(
                array(
                    'selectionMethod' => RelationList::SELECTION_DROPDOWN,
                    'selectionDefaultLocation' => 'foo',
                )
            ),
            array(
                array(
                    'selectionMethod' => RelationList::SELECTION_DROPDOWN,
                    'selectionDefaultLocation' => 'foo',
                    'selectionContentTypes' => array( 1, 2, 3 )
                )
            ),
        );
    }

    /**
     * Provide data sets with field settings which are considered invalid by the
     * {@link validateFieldSettings()} method. The method must return a
     * non-empty array of validation error when receiving such field settings.
     *
     * ATTENTION: This is a default implementation, which must be overwritten
     * if a FieldType supports field settings!
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of field settings.
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          true,
     *      ),
     *      array(
     *          array( 'nonExistentKey' => 2 )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInValidFieldSettings()
    {
        return array(
            array(
                // Missing mandatory keys 'selectionMethod' and 'selectionDefaultLocation'
                array()
            ),
            array(
                // Missing mandatory key 'selectionDefaultLocation'
                array(
                    'selectionMethod' => RelationList::SELECTION_DROPDOWN,
                )
            ),
            array(
                // Missing mandatory key 'selectionMethod'
                array(
                    'selectionDefaultLocation' => 23
                )
            ),
            array(
                // Invalid value for 'selectionMethod'
                array(
                    'selectionMethod' => true,
                    'selectionDefaultLocation' => 23
                )
            ),
            array(
                // Invalid value for 'selectionDefaultLocation'
                array(
                    'selectionMethod' => RelationList::SELECTION_DROPDOWN,
                    'selectionDefaultLocation' => array()
                )
            ),
            array(
                // Invalid value for 'selectionContentTypes'
                array(
                    'selectionMethod' => RelationList::SELECTION_DROPDOWN,
                    'selectionDefaultLocation' => 23,
                    'selectionContentTypes' => true,
                )
            ),
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Relation\Type::getRelations
     */
    public function testGetRelations()
    {
        $ft = $this->createFieldTypeUnderTest();
        $this->assertEquals(
            array(
                Relation::FIELD => array( 70, 72 ),
            ),
            $ft->getRelations( $ft->acceptValue( array( 70, 72 ) ) )
        );
    }
}
