<?php
/**
 * File containing the eZ\Publish\API\Repository\Tests\Stubs\FieldTypeServiceStub
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\API\Repository
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\API\Repository\Tests\Stubs\Exceptions\NotFoundExceptionStub;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\FieldTypeService}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\FieldTypeService
 */
class FieldTypeServiceStub implements FieldTypeService
{
    /**
     * Field types
     *
     * @var \eZ\Publish\API\Repository\Tests\Stubs\FieldTypeStub[]
     */
    protected $fieldTypes = array();

    public function __construct()
    {
        $this->fieldTypes['ezurl'] = new FieldTypeStub( 'ezurl' );
    }

    /**
     * Returns a list of all field types.
     *
     * @return \eZ\Publish\API\Repository\FieldType[]
     */
    public function getFieldTypes()
    {
        return array_values( $this->fieldTypes );
    }

    /**
     * Returns the FieldType registered with the given identifier
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\FieldType
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *         if there is no FieldType registered with $identifier
     */
    public function getFieldType( $identifier )
    {
        if ( !$this->hasFieldType( $identifier ) )
        {
            throw new NotFoundExceptionStub( 'What error code should be used?' );
        }
        return $this->fieldTypes[$identifier];
    }

    /**
     * Returns if there is a FieldType registered under $identifier
     *
     * @param string $identifier
     *
     * @return boolean
     */
    public function hasFieldType( $identifier )
    {
        return isset( $this->fieldTypes[$identifier] );
    }
}
