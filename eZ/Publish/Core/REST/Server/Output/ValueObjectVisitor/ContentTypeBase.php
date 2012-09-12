<?php
/**
 * File containing the ContentTypeBase ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\UrlHandler,
    eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor,
    eZ\Publish\Core\REST\Common\Output\Generator,
    eZ\Publish\Core\REST\Common\Output\Visitor,

    eZ\Publish\API\Repository\Values;

/**
 * Base for ContentType related value object visitors
 */
abstract class ContentTypeBase extends ValueObjectVisitor
{
    /**
     * Returns a suffix for the URL type to generate on basis of the given
     * $contentTypeStatus.
     *
     * @param int $contentTypeStatus
     * @return string
     */
    protected function getUrlTypeSuffix( $contentTypeStatus )
    {
        switch ( $contentTypeStatus )
        {
            case Values\ContentType\ContentType::STATUS_DEFINED:
                return '';

            case Values\ContentType\ContentType::STATUS_DRAFT:
                return 'Draft';

            case Values\ContentType\ContentType::STATUS_MODIFIED:
                return 'Modified';
        }
    }
}
