<?php
/**
 * File containing the ContentCreateStructStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Values\Content;

use \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use \eZ\Publish\API\Repository\Values\ContentType\ContentType;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct
 */
class ContentCreateStructStub extends ContentCreateStruct
{
    /**
     * Instantiates a new content create struct.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param string $mainLanguageCode
     */
    public function __construct( ContentType $contentType, $mainLanguageCode )
    {
        parent::__construct(
            array(
                'contentType'       =>  $contentType,
                'mainLanguageCode'  =>  $mainLanguageCode
            )
        );
    }

    /**
     * Adds a field to the field collection.
     *
     * This method could also be implemented by a magic setter so that
     * $fields[$fieldDefIdentifier][$language] = $value or without language $fields[$fieldDefIdentifier] = $value
     * is an equivalent call.
     *
     * @param string $fieldDefIdentifier the identifier of the field definition
     *
     * @param mixed $value Either a plain value which is understandable by the corresponding
     *                     field type or an instance of a Value class provided by the field type
     *
     * @param string|null $language If not given on a translatable field the initial language is used
     */
    public function setField( $fieldDefIdentifier, $value, $language = null )
    {
        // TODO: Implement setField() method.
    }
}