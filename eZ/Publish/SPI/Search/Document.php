<?php
/**
 * File containing the eZ\Publish\SPI\Search\Document class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\SPI\Search;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * Class for Document for indexing use
 */
class Document extends ValueObject
{
    /**
     * Name of the document field. Will be used to query this field.
     *
     * @var \eZ\Publish\SPI\Search\Field[]|\eZ\Publish\SPI\Search\ChildDocuments[]
     */
    public $members;

    /**
     * Construct Document with member items
     *
     * @param \eZ\Publish\SPI\Search\Field[]|\eZ\Publish\SPI\Search\ChildDocuments[] $members
     */
    public function __construct( array $members )
    {
        $this->members  = $members;
    }
}

