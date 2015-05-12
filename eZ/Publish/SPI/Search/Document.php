<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Content\Search\Document class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Search;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Base class for documents.
 */
class Document extends ValueObject
{
    /**
     * An array of fields
     *
     * @var \eZ\Publish\SPI\Search\Field[]
     */
    public $fields = array();

    /**
     * Translation language code that the documents represents
     *
     * @var string
     */
    public $languageCode;

    /**
     * An array of sub-documents
     *
     * @var \eZ\Publish\SPI\Search\Document[]
     */
    public $documents = array();
}
