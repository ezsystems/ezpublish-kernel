<?php
/**
 * File containing the FieldIdentifierStruct class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Query\Criterion;

/**
 * This struct is used to reference a ContentType Field in content queries / criteria
 */
class FieldIdentifierStruct
{
    /**
     * Constructs a new FieldIdentifierStruct for $contentTypeIdentifier and $fieldIdentifier
     *
     * @param string $contentTypeIdentifier
     * @param string $fieldIdentifier
     */
    public function __construct( $contentTypeIdentifier, $fieldIdentifier )
    {
        $this->contentTypeIdentifier = $contentTypeIdentifier;
        $this->fieldIdentifier = $fieldIdentifier;
    }

    /**
     * ContentType identifier
     * @var string
     */
    public $contentTypeIdentifier;

    /**
     * Field identifier
     * @var string
     */
    public $fieldIdentifier;
}
