<?php
/**
 * File containing the RestContentInfo class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Subset of ContentInfo submitted by REST + some info submitted in addition
 */
class RestContentInfo extends ValueObject
{
    protected $id;
    protected $name;
    protected $contentTypeId;
    protected $ownerId;
    protected $modificationDate;
    protected $publishedDate;
    protected $published;
    protected $alwaysAvailable;
    protected $remoteId;
    protected $mainLanguageCode;
    protected $mainLocationId;
    protected $sectionId;

    protected $versionListReference;
    protected $currentVersionReference;
    protected $locationListReference;
}
