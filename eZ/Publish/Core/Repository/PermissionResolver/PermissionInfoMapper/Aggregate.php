<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\PermissionResolver\PermissionInfoMapper;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\User\UserReference;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Repository\PermissionResolver\PermissionInfoMapper;

/**
 * todo
 */
class Aggregate extends PermissionInfoMapper
{
    /**
     * @var \eZ\Publish\Core\Repository\PermissionResolver\PermissionInfoMapper[]
     */
    private $mappers;

    /**
     * @param \eZ\Publish\Core\Repository\PermissionResolver\PermissionInfoMapper[] $mappers
     */
    public function __construct(array $mappers = [])
    {
        foreach ($mappers as $mapper) {
            $this->addMapper($mapper);
        }
    }

    /**
     * @param \eZ\Publish\Core\Repository\PermissionResolver\PermissionInfoMapper $mapper
     */
    public function addMapper(PermissionInfoMapper $mapper)
    {
        $this->mappers[] = $mapper;
    }

    public function canMap(ValueObject $object)
    {
        return true;
    }

    public function map(ValueObject $object, UserReference $userReference)
    {
        foreach ($this->mappers as $mapper) {
            if ($mapper->canMap($object)) {
                return $mapper->map($object, $userReference);
            }
        }

        throw new NotImplementedException(
            'No mapper available for: ' . get_class($object)
        );
    }
}
