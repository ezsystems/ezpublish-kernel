<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\PermissionResolver\PermissionInfoMapper;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\User\UserReference;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Repository\PermissionResolver\PermissionInfoMapper;
use eZ\Publish\Core\Repository\PermissionResolver\PermissionResolver;
use eZ\Publish\Core\Repository\Values\PermissionInfo;

/**
 * todo
 */
class Content extends PermissionInfoMapper
{
    private $permissionResolver;

    public function __construct(PermissionResolver $permissionResolver)
    {
        $this->permissionResolver = $permissionResolver;
    }

    private function getModuleFunctionMap()
    {
        return [
            'read' => [],
            'edit' => [
                'LanguageLimitation'
            ],
            'remove' => [],
        ];
    }

    public function canMap(ValueObject $object)
    {
        return (
            $object instanceof APIContent ||
            $object instanceof VersionInfo ||
            $object instanceof ContentInfo
        );
    }

    /**
     * todo
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $userReference
     *
     * @return \eZ\Publish\Core\Repository\PermissionResolver\Permission[]
     */
    private function getPermissions(UserReference $userReference)
    {
        return $this->permissionResolver->getPermissions($userReference, 'content');
    }

    public function map(ValueObject $object, UserReference $userReference)
    {
        $permissions = $this->getPermissions($userReference);
        $functionMap = $this->getModuleFunctionMap();
        $permissionMap = [];

        foreach ($functionMap as $function => $limitations) {
            $permissionMap[$function] = $this->permissionResolver->resolvePermissions(
                'content',
                $function,
                $permissions,
                $object,
                $userReference
            );
        }

        return new PermissionInfo(
            [
                'object' => $object,
                'userReference' => $userReference,
                'permissionMap' => $permissionMap,
            ]
        );
    }
}
