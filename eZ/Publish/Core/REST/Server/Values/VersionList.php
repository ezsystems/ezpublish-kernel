<?php

/**
 * File containing the VersionList class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * Version list view model.
 */
class VersionList extends RestValue
{
    /**
     * Versions.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo[]
     */
    public $versions;

    /**
     * Path used to retrieve this version list.
     *
     * @var string
     */
    public $path;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo[] $versions
     * @param string $path
     */
    public function __construct(array $versions, $path)
    {
        $this->versions = $versions;
        $this->path = $path;
    }
}
