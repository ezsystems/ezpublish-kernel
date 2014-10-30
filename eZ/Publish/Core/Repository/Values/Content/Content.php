<?php
/**
 * File containing the eZ\Publish\Core\Repository\Values\Content\Content class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent;

/**
 *
 * this class represents a content object in a specific version
 *
 * @see \eZ\Publish\API\Repository\Values\Content\Content
 */
class Content extends APIContent
{
    use ContentTrait;

    /**
     * Constructs User object
     *
     * @param array $data Must contain the following properties:
     * - internalFields (for ContentTrait)
     * - versionInfo (for ContentTrait)
     */
    public function __construct( array $data = array() )
    {
        $this->init( $data );
    }
}
