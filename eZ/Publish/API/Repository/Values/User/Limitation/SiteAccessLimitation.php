<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\SiteAccessLimitation class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation;

class SiteAccessLimitation extends Limitation
{
    /**
     * @see \eZ\Publish\API\Repository\Values\User\Limitation::getIdentifier()
     *
     * @return string
     */
    public function getIdentifier()
    {
        return Limitation::SITEACCESS;
    }

    /**
     * A hash of human readable limitations, using IDs or identifiers as keys.
     *
     * @readonly
     *
     * @return mixed[]
     */
    public function limitationValuesAsText()
    {
        return $this->limitationValues; // TODO: load site access names
    }
}
