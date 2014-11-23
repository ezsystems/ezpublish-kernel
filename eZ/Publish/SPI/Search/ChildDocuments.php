<?php
/**
 * File containing the eZ\Publish\SPI\Search\ChildDocument class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\SPI\Search;

/**
 * Class for Child Documents for indexing use
 *
 * Child documents are typically used for light relational like features such as nested, parent/child documents
 * for use with block join or parent/child queries.
 */
class ChildDocuments extends Document
{
    /**
     * Name of the document field. Will be used to query this field.
     *
     * @var \eZ\Publish\SPI\Search\Document[]
     */
    public $members;

    /**
     * Construct Document with member (field) items
     *
     * @param \eZ\Publish\SPI\Search\Document[] $members
     */
    public function __construct( array $members )
    {
        $this->members  = $members;
    }
}

