<?php
/**
 * File containing the ContentTypeGroupRefList class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Values;

use eZ\Publish\Core\REST\Client\ContentTypeService;

/**
 * ContentTypeGroupRefList
 */
class ContentTypeGroupRefList
{
    /**
     * Contains ContentTypeGroupRefList reference
     *
     * @var string
     */
    public $listReference;

    /**
     * Contains ContentTypeGroup references
     *
     * @var string[]
     */
    protected $contentTypeGroupReferences;

    /**
     * Content type service
     *
     * @var ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @param \eZ\Publish\Core\REST\Client\ContentTypeService $contentTypeService
     * @param string $listReference
     * @param string[] $contentTypeGroupReferences
     */
    public function __construct( ContentTypeService $contentTypeService, $listReference, array $contentTypeGroupReferences )
    {
        $this->contentTypeService = $contentTypeService;
        $this->listReference = $listReference;
        $this->contentTypeGroupReferences = $contentTypeGroupReferences;
    }

    /**
     * Fetches and returns the ContentTypeGroups contained in the list
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    public function getContentTypeGroups()
    {
        $contentTypeGroups = array();
        foreach ( $this->contentTypeGroupReferences as $reference )
        {
            $contentTypeGroups[] = $this->contentTypeService->loadContentTypeGroup( $reference );
        }
        return $contentTypeGroups;
    }
}
