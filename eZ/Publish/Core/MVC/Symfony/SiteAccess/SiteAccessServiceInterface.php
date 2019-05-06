<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;

/**
 * Provides methods for accessing Site Access information.
 */
interface SiteAccessServiceInterface
{
    /**
     * Returns true if SiteAccess with given name is defined.
     *
     * @param string $name
     *
     * @return bool
     */
    public function exists(string $name): bool;

    /**
     * Returns Site Access with a given name.
     *
     * @param string $name
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    public function get(string $name): SiteAccess;

    /**
     * Returns all available Site Accesses.
     *
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess[]
     */
    public function getAll(): iterable;

    /**
     * Returns all available Site Access Groups.
     *
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccessGroup[]
     */
    public function getAvailableGroups(): iterable;
}
