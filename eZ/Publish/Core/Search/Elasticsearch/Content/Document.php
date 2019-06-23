<?php

/**
 * File containing the Elasticsearch Document class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Describes a document to be indexed in Elasticsearch index storage.
 *
 * @deprecated
 */
class Document extends ValueObject
{
    /**
     * Id of a document.
     *
     * @var int|string
     */
    public $id;

    /**
     * Type of a document.
     *
     * @var string
     */
    public $type;

    /**
     * An array of fields describing a document.
     *
     * @var \eZ\Publish\SPI\Search\Field[]
     */
    public $fields = [];
}
