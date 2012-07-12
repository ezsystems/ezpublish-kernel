<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\RepositoryTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\FieldType;
use eZ\Publish\API\Repository,
    eZ\Publish\Core\FieldType\Url\Value as UrlValue;

/**
 * Integration test for use field type
 *
 * @group integration
 * @group field-type
 */
class UrlFieldTypeIntergrationTest extends BaseIntegrationTest
{
    /**
     * Get name of tested field tyoe
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezurl';
    }

    /**
     * Get field definition data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function getFieldDefinitionData()
    {
        return array(
            // The url field type does not have any special field definition
            // properties, so there is nothing to check for
            array( 'fieldTypeIdentifier', 'ezurl' ),
            array( 'fieldSettings', null ),
            array( 'validatorConfiguration', null ),
        );
    }

    /**
     * Get initial field externals data
     *
     * @return array
     */
    public function getInitialFieldData()
    {
        return new UrlValue( 'http://example.com', 'Example' );
    }

    /**
     * Get externals field data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function getExternalsFieldData()
    {
        return array(
            array( 'link', 'http://example.com' ),
            array( 'text', 'Example' ),
        );
    }

    /**
     * Get update field externals data
     *
     * @return array
     */
    public function getUpdateFieldData()
    {
        return new UrlValue( 'http://example.com/2', 'Example  2' );
    }

    /**
     * Get externals updated field data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function getUpdatedExternalsFieldData()
    {
        return array(
            array( 'link', 'http://example.com/2' ),
            array( 'text', 'Example  2' ),
        );
    }

    /**
     * Get externals copied field data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function getCopiedExternalsFieldData()
    {
        return array(
            array( 'link', 'http://example.com' ),
            array( 'text', 'Example' ),
        );
    }

    /**
     * Get expectation for the toHash call on our field value
     *
     * @return mixed
     */
    public function getToHashExpectation()
    {
        return 'toBeDefined';
    }

    /**
     * Get hashes and their respective converted values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function getHashes()
    {
        return array(
            array( 'toBeDefined', array() ),
        );
    }
}

