<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\ContentType;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used for creating a content type group
 */
class ContentTypeGroupCreateStruct extends ValueObject
{
    /**
     * Readable string identifier of a group
     *
     * @required
     *
     * @var string
     */
    public $identifier;

    /**
     * if set this value overrides the current user as creator
     *
     * @var mixed
     */
    public $creatorId = null;

    /**
     * If set this value overrides the current time for creation
     *
     * @var \DateTime
     */
    public $creationDate = null;

    /**
     * the main language code
     *
     * @since 5.0
     *
     * @var string
     */
    public $mainLanguageCode;

    /**
     * An array of names with languageCode keys
     *
     * @var array an array of string
     */
    public $names;

    /**
     * An array of descriptions with languageCode keys
     *
     * @var array an array of string
     */
    public $descriptions;

}
