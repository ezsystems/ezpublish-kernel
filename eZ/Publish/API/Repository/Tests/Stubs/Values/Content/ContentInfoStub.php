<?php
/**
 * File containing the ContentInfoStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Values\Content;

use \eZ\Publish\API\Repository\Values\Content\ContentInfo;
use \eZ\Publish\API\Repository\Values\ContentType\ContentType;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\Values\Content\ContentInfo}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\Content\ContentInfo
 */
class ContentInfoStub extends ContentInfo
{
    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected $contentType;

    /**
     * The content type of this content object
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function getContentType()
    {
        return $this->contentType;
    }

}