<?php
/**
 * File containing the ContentTypeGroup class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\PublicAPI\Values\ContentType;
use ezp\PublicAPI\Values\ValueObject;

/**
 *
 * This class represents a content type group value
 * @property-read names calls getNames() or on access getName($language)
 * @property-read descriptions calls getDescriptions() or on access getDescription($language)
 *
 */
class ContentTypeGroup extends ValueObject
{
    /**
     * Primary key
     *
     * @var mixed
     */
    public $id;

    /**
     * Readable string identifier of a group
     *
     * @var string
     */
    public $identifier;

    /**
     * Created date (timestamp)
     *
     * @var int
     */
    public $created;

    /**
     * Modified date (timestamp)
     *
     * @var int
     */
    public $modified;

    /**
     * Creator user id
     *
     * @var mixed
     */
    public $creatorId;

    /**
     * Modifier user id
     *
     * @var mixed
     *
     */
    public $modifierId;


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
    public abstract function getNames();

    /**
     * 5.x only
     * this method returns the name of the content type in the given language
     * @param string $languageCode
     * @return string the name for the given language or null if none existis.
     */
    public abstract function getName($languageCode);

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
    public abstract function getDescriptions();

    /**
     * 5.x only
     * this method returns the name of the content type in the given language
     * @param string $languageCode
     * @return string the description for the given language or null if none existis.
     */
    public abstract function getDescription($languageCode);



}
?>
