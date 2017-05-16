<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values;

use eZ\Publish\API\Repository\Values\PermissionInfo as APIPermissionInfo;

class PermissionInfo extends APIPermissionInfo
{
    private $permissionMap;

    public function __construct(array $properties)
    {
        if (isset($properties['permissionMap'])) {
            $this->permissionMap = $properties['permissionMap'];
            unset($properties['permissionMap']);
        }

        parent::__construct($properties);
    }

    /**
     * @param string $function
     *
     * @return boolean
     */
    public function canUser($function)
    {
        if (isset($this->permissionMap['*'])) {
            return true;
        }

        return isset($this->permissionMap[$function]);
    }

    /**
     * @return mixed
     */
    public function getHash()
    {
        return $this->permissionMap;
    }
}
