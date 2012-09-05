<?php
/**
 * File containing the RestContent class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Content;

/**
 * REST Content, as received by /content/objects/<ID>
 *
 * Might have a "Version" (aka Content in the PAPI) embedded
 */
class RestContent
{
    /**
     * @var eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public $contentInfo;

    /**
     * @var eZ\Publish\API\Repository\Values\Content\Content
     */
    public $currentVersion;

    public function __construct( ContentInfo $contentInfo, Content $currentVersion = null )
    {
        $this->contentInfo = $contentInfo;
        $this->currentVersion = $currentVersion;
    }
}
