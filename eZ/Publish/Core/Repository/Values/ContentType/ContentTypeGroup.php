<?php
/**
 * File containing the ContentTypeGroup class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup as APIContentTypeGroup;

/**
 * This class represents a content type group value
 *
 * @property-read string[] $names calls getNames() or on access getName($language)
 * @property-read string[] $descriptions calls getDescriptions() or on access getDescription($language)
 * @property-read mixed $id the id of the content type group
 * @property-read string $identifier the identifier of the content type group
 * @property-read \DateTime $creationDate the date of the creation of this content type group
 * @property-read \DateTime $modificationDate the date of the last modification of this content type group
 * @property-read mixed $creatorId the user id of the creator of this content type group
 * @property-read mixed $modifierId the user id of the user which has last modified this content type group
 * @property-read string $mainLanguageCode 5.0, the main language of the content type group names and description used for fallback.
 */
class ContentTypeGroup extends APIContentTypeGroup
{
    /**
     * @var string[]
     */
    protected $names;

    /**
     * @var string[]
     */
    protected $descriptions;

    /**
     * This method returns the human readable name in all provided languages
     * of the content type
     *
     * The structure of the return value is:
     * <code>
     * array( 'eng' => '<name_eng>', 'de' => '<name_de>' );
     * </code>
     *
     * @since 5.0
     *
     * @return string[]
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * This method returns the name of the content type in the given language
     *
     * @since 5.0
     *
     * @param string $languageCode
     *
     * @return string the name for the given language or null if none exists.
     */
    public function getName( $languageCode )
    {
        if ( array_key_exists( $languageCode, $this->names ) )
        {
            return $this->names[$languageCode];
        }

        return null;
    }

    /**
     * This method returns the human readable description of the content type
     *
     * The structure of this field is:
     * <code>
     * array( 'eng' => '<description_eng>', 'de' => '<description_de>' );
     * </code>
     *
     * @since 5.0
     *
     * @return string[]
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * This method returns the name of the content type in the given language
     *
     * @since 5.0
     *
     * @param string $languageCode
     *
     * @return string the description for the given language or null if none exists.
     */
    public function getDescription( $languageCode )
    {
        if ( array_key_exists( $languageCode, $this->descriptions ) )
        {
            return $this->descriptions[$languageCode];
        }

        return null;
    }
}
