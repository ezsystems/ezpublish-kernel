<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository;

/**
 * An implementation of this class provides access to FieldTypes.
 *
 * @see \eZ\Publish\API\Repository\FieldType
 */
interface FieldTypeService
{
    /**
     * Returns a list of all field types.
     *
     * @return \eZ\Publish\API\Repository\FieldType[]
     */
    public function getFieldTypes(): iterable;

    /**
     * Returns the FieldType registered with the given identifier.
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\FieldType
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if there is no FieldType registered with $identifier
     */
    public function getFieldType(string $identifier): FieldType;

    /**
     * Returns if there is a FieldType registered under $identifier.
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function hasFieldType(string $identifier): bool;
}
