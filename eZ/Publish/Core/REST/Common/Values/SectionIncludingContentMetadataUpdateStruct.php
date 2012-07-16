<?php
/**
 * File containing the SectionIncludingContentMetadataUpdateStruct
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Values;

/**
 * Extended ContentMetadataUpdateStruct that includes section information.
 */
class SectionIncludingContentMetadataUpdateStruct
    extends \eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct
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
