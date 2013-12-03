<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Asset\AssetCreate class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Asset;

use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;

abstract class AssetUpdateStruct extends ContentCreateStruct
{
    /**
     * URI to read the Asset to create from
     *
     * @var string
     */
    public $sourceUri;
}
