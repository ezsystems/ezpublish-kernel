<?php

/**
 * File containing the eZ\Publish\Core\FieldType\RichText\InternalLinkValidator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\RichText;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Validator for RichText internal format links.
 */
class InternalLinkValidator
{
    /**
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     */
    public function __construct(ContentService $contentService, LocationService $locationService)
    {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
    }

    /**
     * Validates following link formats: 'ezcontent://<contentId>', 'ezremote://<contentRemoteId>', 'ezlocation://<locationId>'.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If given $scheme is not supported.
     *
     * @param string $scheme
     * @param string $id
     *
     * @return bool
     */
    public function validate($scheme, $id)
    {
        try {
            switch ($scheme) {
                case 'ezcontent':
                    $this->contentService->loadContentInfo($id);
                    break;
                case 'ezremote':
                    $this->contentService->loadContentByRemoteId($id);
                    break;
                case 'ezlocation':
                    $this->locationService->loadLocation($id);
                    break;
                default:
                    throw new InvalidArgumentException($scheme, "Given scheme '{$scheme}' is not supported.");
            }
        } catch (NotFoundException $e) {
            return false;
        }

        return true;
    }
}
