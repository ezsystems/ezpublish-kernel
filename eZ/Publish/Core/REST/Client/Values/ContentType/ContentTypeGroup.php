<?php

/**
 * File containing the ContentTypeGroup class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup as APIContentTypeGroup;

class ContentTypeGroup extends APIContentTypeGroup
{
    protected $names;

    protected $descriptions;

    public function __construct(array $data = array())
    {
        foreach ($data as $propertyName => $propertyValue) {
            $this->$propertyName = $propertyValue;
        }
    }

    /**
     * 5.x only
     * This method returns the human readable name in all provided languages
     * of the content type.
     *
     * The structure of the return value is:
     * <code>
     * array( 'eng' => '<name_eng>', 'de' => '<name_de>' );
     * </code>
     *
     * @return string[]
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * 5.x only
     * This method returns the name of the content type in the given language.
     *
     * @param string $languageCode
     *
     * @return string the name for the given language or null if none exists.
     */
    public function getName($languageCode)
    {
        return $this->names[$languageCode];
    }

    /**
     * 5.x only
     * This method returns the human readable description of the content type
     * The structure of this field is:
     * <code>
     * array( 'eng' => '<description_eng>', 'de' => '<description_de>' );
     * </code>.
     *
     * @return string[]
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * 5.x only
     * This method returns the name of the content type in the given language.
     *
     * @param string $languageCode
     *
     * @return string the description for the given language or null if none exists.
     */
    public function getDescription($languageCode)
    {
        return $this->descriptions[$languageCode];
    }
}
