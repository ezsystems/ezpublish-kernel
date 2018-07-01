<?php

/**
 * File containing the FileListerRegistry interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration;

/**
 * A registry of FileListerInterfaces.
 */
interface FileListerRegistry
{
    /**
     * Returns the FileListerInterface matching the argument.
     *
     * @param string $identifier An identifier string.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If no FileListerInterface exists with this identifier
     *
     * @return \eZ\Bundle\EzPublishIOBundle\Migration\FileListerInterface The FileListerInterface given by the identifier.
     */
    public function getItem($identifier);

    /**
     * Returns the identifiers of all registered FileListerInterfaces.
     *
     * @return string[] Array of identifier strings.
     */
    public function getIdentifiers();
}
