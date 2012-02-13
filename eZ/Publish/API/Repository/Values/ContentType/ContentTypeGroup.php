<?php
/**
 * File containing the ContentTypeGroup class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ContentType;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a content type group value
 * 
 * @property-read names calls getNames() or on access getName($language)
 * @property-read descriptions calls getDescriptions() or on access getDescription($language)
 * @property-read int $id the id of the content type group
 * @property-read string $identifier the identifier of the content type group
 * @property-read DateTime $createdDate the date of the creation of this content type group
 * @property-read DateTime $modificationDate the date of the last modification of this content type group
 * @property-read int $creatorId the user id of the creator of this content type group
 * @property-read int $modifierId the user id of the user which has last modified this content type group
 *
 */
abstract class ContentTypeGroup extends ValueObject
{
    /**
     * Primary key
     *
     * @var mixed
     */
    protected $id;

    /**
     * Readable string identifier of a group
     *
     * @var string
     */
    protected $identifier;

    /**
     * Created date (timestamp)
     *
     * @var DateTime
     */
    protected $createdDate;

    /**
     * Modified date (timestamp)
     *
     * @var DateTime
     */
    protected $modifiedDate;

    /**
     * Creator user id
     *
     * @var mixed
     */
    protected $creatorId;

    /**
     * Modifier user id
     *
     * @var mixed
     *
     */
    protected $modifierId;

    /**
     * 5.x only
     * This method returns the human readable name in all provided languages
     * of the content type
     *
     * The structure of the return value is:
     * <code>
     * array( 'eng' => '<name_eng>', 'de' => '<name_de>' );
     * </code>
     *
     * @return string[]
     */
    abstract public function getNames();

    /**
     * 5.x only
     * this method returns the name of the content type in the given language
     * @param string $languageCode
     * @return string the name for the given language or null if none existis.
     */
    abstract public function getName( $languageCode );

    /**
     *  5.x only
     * This method returns the human readable description of the content type
     * The structure of this field is:
     * <code>
     * array( 'eng' => '<description_eng>', 'de' => '<description_de>' );
     * </code>
     *
     * @return string[]
     */
    abstract public function getDescriptions();

    /**
     * 5.x only
     * this method returns the name of the content type in the given language
     * @param string $languageCode
     * @return string the description for the given language or null if none existis.
     */
    abstract public function getDescription( $languageCode );
}
