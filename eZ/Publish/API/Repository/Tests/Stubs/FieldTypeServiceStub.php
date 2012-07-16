<?php
/**
 * File containing the eZ\Publish\API\Repository\Tests\Stubs\FieldTypeServiceStub
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\API\Repository
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;
use eZ\Publish\API\Repository\FieldTypeService;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\FieldTypeService}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\FieldTypeService
 */
class FieldTypeServiceStub implements FieldTypeService
{
    /**
     * Returns a list of all field types.
     *
     * @return \eZ\Publish\API\Repository\FieldType[]
     */
    public function getFieldTypes()
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * Returns the FieldType registered with the given identifier
     *
     * @param string $identifier
     * @return \eZ\Publish\API\Repository\FieldType
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *         if there is no FieldType registered with $identifier
     */
    public function getFieldType( $identifier )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * Returns if there is a FieldType registered under $identifier
     *
     * @param string $identifier
     * @return bool
     */
    public function hasFieldType( $identifier )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }
}
