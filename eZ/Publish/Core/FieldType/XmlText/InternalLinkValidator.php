<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\InternalLinkValidator class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Validator for XmlText internal format links.
 */
class InternalLinkValidator
{
    /**
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     */
    public function __construct( ContentService $contentService, LocationService $locationService )
    {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
    }

    /**
     * Validates following link formats: 'ezcontent://<contentId>', 'ezremote://<contentRemoteId>', 'ezlocation://<locationId>'
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If given $protocol is not supported.
     *
     * @param string $protocol
     * @param string $id
     *
     * @return boolean
     */
    public function validate( $protocol, $id )
    {
        try
        {
            switch ( $protocol )
            {
                case "ezcontent":
                    $this->contentService->loadContentInfo( $id );
                    break;
                case "ezremote";
                    $this->contentService->loadContentByRemoteId( $id );
                    break;
                case "ezlocation":
                    $this->locationService->loadLocation( $id );
                    break;
                default:
                    throw new InvalidArgumentException( $protocol, "Given protocol is not supported." );
            }
        }
        catch ( NotFoundException $e )
        {
            return false;
        }

        return true;
    }
}
