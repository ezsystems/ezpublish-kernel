<?php
/**
 * File containing the RestContentMetadataUpdateStruct
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Values;

use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;

/**
 * Extended ContentMetadataUpdateStruct that includes section information.
 */
class RestContentMetadataUpdateStruct extends ContentMetadataUpdateStruct
{
    /**
     * ID of the section to assign.
     *
     * Leave null to not change section assignment.
     *
     * @var mixed
     */
    public $sectionId;
}
