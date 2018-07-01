<?php

/**
 * File containing the Location class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Location as APILocation;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Values\Content\Location}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\Content\Location
 */
class Location extends APILocation
{
    /**
     * ContentInfo.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected $contentInfo;

    /**
     * Returns the content info of the content object of this location.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function getContentInfo()
    {
        return $this->contentInfo;
    }
}
