<?php
/**
 * File containing the ContentInfo class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Values\Content;

use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use eZ\Publish\Core\REST\Client\ContentTypeService;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Values\Content\ContentInfo}
 * class.
 *
 * @property-read mixed $id The unique id of the content object
 * @property-read mixed $contentTypeId The unique id of the content type object this content is an instance of
 * @property-read string $name the computed name (via name schema) in the main language of the content object
 * @property-read mixed $sectionId the section to which the content is assigned
 * @property-read int $currentVersionNo Current Version number is the version number of the published version or the version number of a newly created draft (which is 1).
 * @property-read boolean $published true if there exists a published version false otherwise
 * @property-read mixed $ownerId the user id of the owner of the content
 * @property-read \DateTime $modificationDate Content modification date
 * @property-read \DateTime $publishedDate date of the last publish operation
 * @property-read boolean $alwaysAvailable Indicates if the content object is shown in the mainlanguage if its not present in an other requested language
 * @property-read string $remoteId a global unique id of the content object
 * @property-read string $mainLanguageCode The main language code of the content. If the available flag is set to true the content is shown in this language if the requested language does not exist.
 * @property-read mixed $mainLocationId Identifier of the main location.
 * @see \eZ\Publish\API\Repository\Values\Content\ContentInfo
 */
class ContentInfo extends \eZ\Publish\API\Repository\Values\Content\ContentInfo
{
    /**
     * @var int
     */
    protected $contentTypeId;

    /**
     * @var \eZ\Publish\Core\REST\Client\ContentTypeService
     */
    protected $contentTypeService;

    public function __construct( ContentTypeService $contentTypeService, array $data = array() )
    {
        parent::__construct( $data );
        $this->contentTypeService = $contentTypeService;
    }

    public function __get( $property )
    {
        return parent::__get( $property );
    }
}
