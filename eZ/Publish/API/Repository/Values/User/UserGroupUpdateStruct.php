<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to update a user group in the repository
 */
class UserGroupUpdateStruct extends ValueObject
{
    /**
     * The update structure for the profile content
     *
     * @var \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct
     */
    public $contentUpdateStruct = null;

    /**
     * The update structure for the profile meta data
     *
     * @var \eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct
     */
    public $contentMetadataUpdateStruct = null;
}
