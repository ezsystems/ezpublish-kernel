<?php
/**
 * File containing the FieldTypeService class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client;

use eZ\Publish\API\Repository\FieldTypeService as APIFieldTypeService;
use eZ\Publish\Core\REST\Common\Exceptions;

class FieldTypeService implements APIFieldTypeService
{
    /**
     * FieldTypes by identifier
     *
     * @var \eZ\Publish\Core\REST\Client\FieldType[]
     */
    protected $fieldTypes = array();

    /**
     * @param \eZ\Publish\Core\REST\Client\FieldType[] $fieldTypes
     *
     * @return void
     */
    public function __construct( array $fieldTypes = array() )
    {
        foreach ( $fieldTypes as $fieldType )
        {
            $this->addFieldType( $fieldType );
        }
    }

    /**
     * Adds the given $fieldType
     *
     * Note, this is not an API method and not meant to be used directly!
     *
     * @param FieldType $fieldType
     *
     * @access protected
     *
     * @return void
     */
    public function addFieldType( FieldType $fieldType )
    {
        $this->fieldTypes[$fieldType->getFieldTypeIdentifier()] = $fieldType;
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
        if ( $this->hasFieldType( $identifier ) )
        {
            return $this->fieldTypes[$identifier];
        }

        throw new Exceptions\NotFoundException(
            sprintf(
                'FieldType with identifier "%s" not found.',
                $identifier
            )
        );
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
