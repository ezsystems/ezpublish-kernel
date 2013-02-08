<?php
/**
 * File containing the RestContentType class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * REST Content type, as received by /content/types/<ID>
 *
 * Might have a "FieldDefinitionList" embedded
 */
class RestContentType extends RestValue
{
    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public $contentType;

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[]
     */
    public $fieldDefinitions;

    /**
     * Construct
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     */
    public function __construct( ContentType $contentType, array $fieldDefinitions = null )
    {
        $this->contentType = $contentType;
        $this->fieldDefinitions = $fieldDefinitions;
    }
}
