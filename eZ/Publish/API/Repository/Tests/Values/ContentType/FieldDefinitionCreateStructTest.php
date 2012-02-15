<?php
/**
 * File containing the FieldDefinitionCreateStructTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\ContentType;
use eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\StringLengthValidatorStub;

use \eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for operations in the FieldDefinitionCreateStruct using in memory storage.
 *
 * @see eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct
 */
class FieldDefinitionCreateStructTest extends BaseTest
{
    /**
     * Test for the setName() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct::setName()
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testNewFieldDefinitionCreateStruct
     */
    public function testSetName()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $fieldDefinitionCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'string'
        );

        $fieldDefinitionCreate->setName( 'John Michael Dorian', 'eng_US' );
        /* END: Use Case */

        $this->assertEquals(
            'John Michael Dorian',
            $fieldDefinitionCreate->names['eng_US']
        );
    }

    /**
     * Test for the setName() method with magic access.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct::setName()
     * @depends eZ\Publish\API\Repository\Tests\Values\ContentType\FieldDefinitionCreateStructTest::testSetName
     */
    public function testSetNameMagicAccess()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $fieldDefinitionCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'string'
        );

        $fieldDefinitionCreate->names['eng_US'] = 'John Michael Dorian';
        /* END: Use Case */

        $this->assertEquals(
            'John Michael Dorian',
            $fieldDefinitionCreate->names['eng_US']
        );
    }

    /**
     * Test for the setDescription() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct::setDescription()
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testNewFieldDefinitionCreateStruct
     */
    public function testSetDescription()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $fieldDefinitionCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'string'
        );

        $fieldDefinitionCreate->setDescription( 'Protagonist and narrator', 'eng_US' );
        /* END: Use Case */

        $this->assertEquals(
            'Protagonist and narrator',
            $fieldDefinitionCreate->descriptions['eng_US']
        );
    }

    /**
     * Test for the setDescription() method with magic access.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct::setDescription()
     * @depends eZ\Publish\API\Repository\Tests\Values\ContentType\FieldDefinitionCreateStructTest::testSetDescription
     */
    public function testSetDescriptionMagicAccess()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $fieldDefinitionCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'string'
        );

        $fieldDefinitionCreate->descriptions['eng_US'] = 'Protagonist and narrator';
        /* END: Use Case */

        $this->assertEquals(
            'Protagonist and narrator',
            $fieldDefinitionCreate->descriptions['eng_US']
        );
    }

    /**
     * Test for the setValidator() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct::setValidator()
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testNewFieldDefinitionCreateStruct
     */
    public function testSetValidator()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $fieldDefinitionCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'string'
        );

        $fieldDefinitionCreate->setValidator( new StringLengthValidatorStub() );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\Validator',
            $fieldDefinitionCreate->validator
        );
    }

    /**
     * Test for the setValidator() method with magic access.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct::setValidator()
     * @depends eZ\Publish\API\Repository\Tests\Values\ContentType\FieldDefinitionCreateStructTest::testSetValidator
     */
    public function testSetValidatorMagicAccess()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $fieldDefinitionCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'string'
        );

        $fieldDefinitionCreate->validator = new StringLengthValidatorStub();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\Validator',
            $fieldDefinitionCreate->validator
        );
    }

    /**
     * Test for the setFieldSettings() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct::setFieldSettings()
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testNewFieldDefinitionCreateStruct
     */
    public function testSetFieldSettings()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $fieldDefinitionCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'string'
        );

        $fieldSettings = array(
            'someSetting' => true
        );
        $fieldDefinitionCreate->setFieldSettings( $fieldSettings );
        /* END: Use Case */

        $this->assertEquals(
            $fieldSettings,
            $fieldDefinitionCreate->fieldSettings
        );
    }

    /**
     * Test for the setFieldSettings() method with magic access.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct::setFieldSettings()
     * @depends eZ\Publish\API\Repository\Tests\Values\ContentType\FieldDefinitionCreateStructTest::testSetFieldSettings
     */
    public function testSetFieldSettingsMagicAccess()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $fieldDefinitionCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'string'
        );

        $fieldSettings = array(
            'someSetting' => true
        );
        $fieldDefinitionCreate->fieldSettings = $fieldSettings;
        /* END: Use Case */

        $this->assertEquals(
            $fieldSettings,
            $fieldDefinitionCreate->fieldSettings
        );
    }
}
